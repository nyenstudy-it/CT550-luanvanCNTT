<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiProductChatService
{
    private const TOKEN_STOP_WORDS = [
        'toi',
        'can',
        'muon',
        'tim',
        'mua',
        'cho',
        've',
        'voi',
        'cua',
        'la',
        'nhu',
        'nao',
        'gi',
        'co',
        'khong',
        'nhung',
        'hay',
        'va',
        'de',
        'duoc',
        'mot',
        'nhieu',
        'loai',
        // REMOVED: 'san', 'pham' - important for product names
        'hang',
        'tot',
        'gia',
        're',
        'xin',
        'tu',
        'tren',
        'duoi',
        'khoang',
        'giu',
        'giup',
        'goi',
        'y',
        'ocop',
        'vnd',
        'vnđ',
        'dong',
        'đ',
        'k',
        'tr',
        'trieu',
        'nghin',
        'em',
        'anh',
        'chi',
        'ban',
        'minh',
        'toi',
        'nhe',
        'a',
        'ah',
    ];

    public function chat(string $message, array $history = []): array
    {
        $filters = $this->extractFilters($message);
        $products = $this->searchProducts($message, $filters);
        $answer = $this->generateAnswer($message, $history, $filters, $products);
        $suggestedQuestions = $this->buildSuggestedQuestions($filters, $products);

        return [
            'answer' => $answer,
            'products' => $products,
            'filters' => $filters,
            'suggested_questions' => $suggestedQuestions,
        ];
    }

    /**
     * Detect if user asked for exact product name match
     * Example: "Trà oolong premium" → match with product "Trà oolong premium"
     */
    private function hasExactProductNameMatch(string $message, Product $product): bool
    {
        $normalizedMessage = $this->normalizeText($message);
        $normalizedProductName = $this->normalizeText($product->name);

        // Exact full match
        if ($normalizedMessage === $normalizedProductName) {
            return true;
        }

        // Check if message contains full product name as substring
        if (str_contains($normalizedMessage, $normalizedProductName)) {
            return true;
        }

        // Check if normalized message exactly matches or closely matches (within 80% similarity)
        $similarity = 0;
        similar_text($normalizedMessage, $normalizedProductName, $similarity);

        return $similarity >= 80;
    }

    private function extractFilters(string $message): array
    {
        $fallback = $this->fallbackFilters($message);

        try {
            $payload = [
                'model' => config('services.openai.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Phan tich cau hoi mua sam va tra ve JSON hop le voi keys: category (string|null), price_min (number|null), price_max (number|null), keywords (array), needs (array). category la danh muc san pham neu co. keywords la tu khoa quan trong. needs la nhu cau su dung. Chi tra ve JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                'temperature' => 0.1,
                'response_format' => [
                    'type' => 'json_object',
                ],
            ];

            $response = $this->openAiRequest($payload);
            $content = data_get($response, 'choices.0.message.content');

            if (!is_string($content) || $content === '') {
                return $fallback;
            }

            $parsed = json_decode($content, true);

            if (!is_array($parsed)) {
                return $fallback;
            }

            return [
                'category' => $this->normalizeString(data_get($parsed, 'category')),
                'price_min' => $this->normalizeNumber(data_get($parsed, 'price_min')),
                'price_max' => $this->normalizeNumber(data_get($parsed, 'price_max')),
                'keywords' => $this->normalizeArray(data_get($parsed, 'keywords', [])),
                'needs' => $this->normalizeArray(data_get($parsed, 'needs', [])),
            ];
        } catch (\Throwable $exception) {
            Log::warning('AI filter extraction failed', ['error' => $exception->getMessage()]);
            return $fallback;
        }
    }

    private function searchProducts(string $message, array $filters): array
    {
        $query = Product::query()
            ->with(['category:id,name', 'variants.inventory', 'primaryImage:id,product_id,image_path', 'images:id,product_id,image_path'])
            ->where('status', 'active');

        $hasCategoryConstraint = false;

        if (!empty($filters['category']) && $this->isSpecificCategory((string) $filters['category'])) {
            $rawCategory = trim((string) $filters['category']);
            $category = $this->normalizeText($rawCategory);
            $escapedCategory = addcslashes($category, '\\%_');
            $escapedRawCategory = addcslashes($rawCategory, '\\%_');
            $query->whereHas('category', function ($builder) use ($escapedCategory, $escapedRawCategory) {
                $builder->where('name', 'like', '%' . $escapedCategory . '%')
                    ->orWhere('name', 'like', '%' . $escapedRawCategory . '%');
            });
            $hasCategoryConstraint = true;
        }

        if (!empty($filters['price_min'])) {
            $query->whereHas('variants', function ($builder) use ($filters) {
                $builder->where('price', '>=', $filters['price_min']);
            });
        }

        if (!empty($filters['price_max'])) {
            $query->whereHas('variants', function ($builder) use ($filters) {
                $builder->where('price', '<=', $filters['price_max']);
            });
        }

        $products = $query->limit(120)->get();

        if ($products->isEmpty() && $hasCategoryConstraint) {
            $products = Product::query()
                ->with(['category:id,name', 'variants.inventory', 'primaryImage:id,product_id,image_path', 'images:id,product_id,image_path'])
                ->where('status', 'active')
                ->when(!empty($filters['price_min']), function ($builder) use ($filters) {
                    $builder->whereHas('variants', function ($variantBuilder) use ($filters) {
                        $variantBuilder->where('price', '>=', $filters['price_min']);
                    });
                })
                ->when(!empty($filters['price_max']), function ($builder) use ($filters) {
                    $builder->whereHas('variants', function ($variantBuilder) use ($filters) {
                        $variantBuilder->where('price', '<=', $filters['price_max']);
                    });
                })
                ->limit(120)
                ->get();
        }

        $tokens = $this->extractSearchTokens($message, $filters);
        $coreTokens = $this->extractCoreTokens($message, $filters);
        $hasStrongIntent = !empty($coreTokens) || !empty($filters['category']) || !empty($filters['price_min']) || !empty($filters['price_max']);
        $tokenCount = max(1, count($coreTokens));
        $isBudgetFocusedQuery = $this->isBudgetFocusedQuery($filters, $coreTokens);

        $scored = $products->map(function (Product $product) use ($tokens, $filters) {
            $variant = $product->variants
                ->sortBy('price')
                ->first(function ($item) {
                    return (int) optional($item->inventory)->quantity > 0;
                }) ?? $product->variants->sortBy('price')->first();

            $stock = $product->variants->reduce(function (int $carry, $item) {
                return $carry + (int) optional($item->inventory)->quantity;
            }, 0);

            $analysis = $this->analyzeProductRelevance($product, $tokens, $filters, $variant ? (float) $variant->price : null, $stock);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => optional($product->category)->name,
                'description' => Str::limit((string) ($product->description ?? ''), 200),
                'price' => $variant ? (float) $variant->price : null,
                'stock' => $stock,
                'url' => url('/products/' . $product->id),
                'image_url' => $this->resolveProductImageUrl($product),
                'match_reasons' => $analysis['reasons'],
                'relevance_score' => $analysis['score'],
                'matched_token_count' => $analysis['matched_token_count'],
                'token_total' => max(1, count($tokens)),
            ];
        })->filter(function (array $item) use ($hasStrongIntent, $tokenCount, $isBudgetFocusedQuery, $filters) {
            if (!$this->passesPriceConstraint($item['price'], $filters)) {
                return false;
            }

            if (!$hasStrongIntent) {
                return $item['stock'] > 0;
            }

            if ($isBudgetFocusedQuery) {
                return $item['stock'] > 0 && $item['price'] !== null;
            }

            $minimumMatched = max(1, (int) ceil($tokenCount * 0.3));
            if ($tokenCount >= 4) {
                $minimumMatched = max(2, $minimumMatched);
            }

            if ($item['relevance_score'] < 11) {
                return false;
            }

            return $item['matched_token_count'] >= $minimumMatched;
        })->sort(function (array $a, array $b) use ($isBudgetFocusedQuery) {
            if ($isBudgetFocusedQuery) {
                $priceA = $a['price'] ?? PHP_FLOAT_MAX;
                $priceB = $b['price'] ?? PHP_FLOAT_MAX;

                if ($priceA === $priceB) {
                    return $b['stock'] <=> $a['stock'];
                }

                return $priceA <=> $priceB;
            }

            if ($a['relevance_score'] === $b['relevance_score']) {
                if ($a['matched_token_count'] === $b['matched_token_count']) {
                    return $b['stock'] <=> $a['stock'];
                }

                return $b['matched_token_count'] <=> $a['matched_token_count'];
            }

            return $b['relevance_score'] <=> $a['relevance_score'];
        })->take(6)->values();

        // Safety net: if strict relevance returns empty but user gave budget/category hints, return best in-stock products by constraints.
        if ($scored->isEmpty() && (!empty($filters['price_min']) || !empty($filters['price_max']) || !empty($filters['category']))) {
            $scored = $products->map(function (Product $product) {
                $variant = $product->variants
                    ->sortBy('price')
                    ->first(function ($item) {
                        return (int) optional($item->inventory)->quantity > 0;
                    }) ?? $product->variants->sortBy('price')->first();

                $stock = $product->variants->reduce(function (int $carry, $item) {
                    return $carry + (int) optional($item->inventory)->quantity;
                }, 0);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => optional($product->category)->name,
                    'description' => Str::limit((string) ($product->description ?? ''), 200),
                    'price' => $variant ? (float) $variant->price : null,
                    'stock' => $stock,
                    'url' => url('/products/' . $product->id),
                    'image_url' => $this->resolveProductImageUrl($product),
                    'match_reasons' => ['Phù hợp theo mức giá/danh mục bạn yêu cầu'],
                ];
            })->filter(function (array $item) use ($filters) {
                return $item['stock'] > 0
                    && $item['price'] !== null
                    && $this->passesPriceConstraint($item['price'], $filters);
            })->sort(function (array $a, array $b) {
                $priceA = $a['price'] ?? PHP_FLOAT_MAX;
                $priceB = $b['price'] ?? PHP_FLOAT_MAX;

                if ($priceA === $priceB) {
                    return $b['stock'] <=> $a['stock'];
                }

                return $priceA <=> $priceB;
            })->take(6)->values();
        }

        return $scored->map(function (array $item) {
            unset($item['relevance_score'], $item['matched_token_count'], $item['token_total']);
            return $item;
        })->all();
    }

    private function resolveProductImageUrl(Product $product): string
    {
        $path = optional($product->primaryImage)->image_path
            ?? optional($product->images->first())->image_path
            ?? $product->image;

        if (!empty($path)) {
            return asset('storage/' . ltrim((string) $path, '/'));
        }

        return asset('frontend/images/product/product-1.jpg');
    }

    private function generateAnswer(string $message, array $history, array $filters, array $products): string
    {
        $history = array_slice($history, -6);

        $historyMessages = [];
        foreach ($history as $item) {
            $role = data_get($item, 'role');
            $content = trim((string) data_get($item, 'content', ''));

            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }

            $historyMessages[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $messages = array_merge([
            [
                'role' => 'system',
                'content' => 'Bạn là trợ lý tư vấn sản phẩm cho website thương mại điện tử. Chỉ được tư vấn dựa trên dữ liệu PRODUCTS được cung cấp. Không được bịa thông tin. Nếu PRODUCTS rỗng hoặc độ phù hợp thấp, hãy nói rõ và hỏi lại 1-2 câu để làm rõ nhu cầu. Trả lời tiếng Việt có dấu, rõ ràng, ngắn gọn.',
            ],
            [
                'role' => 'system',
                'content' => 'FILTERS: ' . json_encode($filters, JSON_UNESCAPED_UNICODE),
            ],
            [
                'role' => 'system',
                'content' => 'PRODUCTS: ' . json_encode($products, JSON_UNESCAPED_UNICODE),
            ],
        ], $historyMessages, [
            [
                'role' => 'user',
                'content' => $message,
            ],
        ]);

        try {
            $response = $this->openAiRequest([
                'model' => config('services.openai.model'),
                'messages' => $messages,
                'temperature' => 0.35,
            ]);

            $content = data_get($response, 'choices.0.message.content');
            if (is_string($content) && trim($content) !== '') {
                return trim($content);
            }
        } catch (\Throwable $exception) {
            Log::warning('AI answer generation failed', ['error' => $exception->getMessage()]);
        }

        return $this->fallbackAnswer($products);
    }

    private function openAiRequest(array $payload): array
    {
        $apiKey = (string) config('services.openai.api_key');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY is missing.');
        }

        $response = Http::timeout(25)
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/chat/completions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI request failed: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json();
    }

    private function fallbackFilters(string $message): array
    {
        $normalized = mb_strtolower($message);
        $priceMax = null;

        if (preg_match('/duoi\s*(\d+[\.,]?\d*)\s*(k|nghin|trieu)?/u', $normalized, $matches)) {
            $value = (float) str_replace(',', '.', $matches[1]);
            $unit = $matches[2] ?? '';

            if (in_array($unit, ['k', 'nghin'], true)) {
                $value *= 1000;
            }

            if ($unit === 'trieu') {
                $value *= 1000000;
            }

            $priceMax = $value;
        }

        $priceMin = null;
        if (preg_match('/(tren|tu)\s*(\d+[\.,]?\d*)\s*(k|nghin|trieu)?/u', $normalized, $matches)) {
            $value = (float) str_replace(',', '.', $matches[2]);
            $unit = $matches[3] ?? '';

            if (in_array($unit, ['k', 'nghin'], true)) {
                $value *= 1000;
            }

            if ($unit === 'trieu') {
                $value *= 1000000;
            }

            $priceMin = $value;
        }

        $normalizedMessage = $this->normalizeText($message);
        $categoryGuess = null;
        $categoryHints = [
            'trà' => ['tra', 'tea'],
            'mật ong' => ['mat ong', 'honey'],
            'cà phê' => ['ca phe', 'coffee'],
            'gạo' => ['gao', 'rice'],
            'tinh dầu' => ['tinh dau', 'essential oil'],
            'mỹ phẩm' => ['my pham', 'lam dep', 'duong da', 'cham soc da'],
        ];

        foreach ($categoryHints as $category => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($normalizedMessage, $this->normalizeText($alias))) {
                    $categoryGuess = $category;
                    break 2;
                }
            }
        }

        return [
            'category' => $categoryGuess,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'keywords' => [],
            'needs' => [],
        ];
    }

    private function fallbackAnswer(array $products): string
    {
        if (empty($products)) {
            return 'Mình chưa tìm thấy sản phẩm phù hợp với tiêu chí hiện tại. Bạn có thể nêu rõ nhu cầu, ngân sách hoặc loại sản phẩm để mình lọc chính xác hơn.';
        }

        $lines = ['Mình gợi ý một số sản phẩm phù hợp:'];
        foreach (array_slice($products, 0, 3) as $index => $product) {
            $priceText = !empty($product['price']) ? number_format((float) $product['price'], 0, ',', '.') . ' VND' : 'Liên hệ';
            $lines[] = ($index + 1) . '. ' . $product['name'] . ' - Giá tham khảo: ' . $priceText;
        }

        $lines[] = 'Bạn muốn ưu tiên theo mức giá, danh mục hay công dụng cụ thể nào?';

        return implode("\n", $lines);
    }

    private function normalizeArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            return $this->normalizeString($item);
        }, $value)));
    }

    private function normalizeString($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }

    private function normalizeNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function passesPriceConstraint(?float $price, array $filters): bool
    {
        if ($price === null) {
            return false;
        }

        if (!empty($filters['price_min']) && $price < (float) $filters['price_min']) {
            return false;
        }

        if (!empty($filters['price_max']) && $price > (float) $filters['price_max']) {
            return false;
        }

        return true;
    }

    private function isSpecificCategory(string $category): bool
    {
        $normalized = $this->normalizeText($category);
        if ($normalized === '') {
            return false;
        }

        $generic = [
            'ocop',
            'san pham',
            'qua',
            'qua tang',
            'lam qua',
            'goi y',
            'hang',
        ];

        return !in_array($normalized, $generic, true);
    }

    private function extractSearchTokens(string $message, array $filters): array
    {
        $raw = implode(' ', array_filter([
            $message,
            (string) ($filters['category'] ?? ''),
            implode(' ', $filters['keywords'] ?? []),
            implode(' ', $filters['needs'] ?? []),
        ]));

        $tokens = $this->tokenize($raw);
        $phrases = $this->extractPhrases($raw);

        $allTokens = array_merge($tokens, $phrases);
        $allTokens = array_values(array_filter($allTokens, function (string $token) {
            return mb_strlen($token) >= 2 && !in_array($token, self::TOKEN_STOP_WORDS, true);
        }));

        $expanded = [];
        $synonyms = $this->relatedTokenMap();

        foreach ($allTokens as $token) {
            $expanded[] = $token;
            if (isset($synonyms[$token])) {
                $expanded = array_merge($expanded, $synonyms[$token]);
            }
        }

        return array_values(array_unique(array_filter($expanded)));
    }

    private function extractCoreTokens(string $message, array $filters): array
    {
        $raw = implode(' ', array_filter([
            $message,
            (string) ($filters['category'] ?? ''),
            implode(' ', $filters['keywords'] ?? []),
            implode(' ', $filters['needs'] ?? []),
        ]));

        $tokens = $this->tokenize($raw);

        return array_values(array_filter($tokens, function (string $token) {
            if (in_array($token, self::TOKEN_STOP_WORDS, true)) {
                return false;
            }

            // Remove pure number tokens from intent strictness, they are already handled by price filters.
            if (preg_match('/^\d+$/', $token)) {
                return false;
            }

            return mb_strlen($token) >= 2;
        }));
    }

    private function isBudgetFocusedQuery(array $filters, array $coreTokens): bool
    {
        $hasBudget = !empty($filters['price_min']) || !empty($filters['price_max']);
        if (!$hasBudget) {
            return false;
        }

        // If user only asks general recommendation + budget (no concrete product intent), prioritize budget ranking.
        return count($coreTokens) <= 1;
    }

    private function relatedTokenMap(): array
    {
        return [
            'tri ho' => ['ho', 'hong', 'siro', 'giam ho'],
            'ho' => ['tri ho', 'hong', 'siro'],
            'mat ngu' => ['ngu ngon', 'thu gian', 'de ngu'],
            'ngu ngon' => ['mat ngu', 'thu gian'],
            'giam can' => ['it duong', 'an kieng', 'healthy'],
            'lam dep' => ['duong da', 'duong toc', 'my pham'],
            'duong da' => ['lam dep', 'my pham', 'duong am'],
            'thanh loc' => ['detox', 'tra', 'moc'],
            'suc khoe' => ['bo duong', 'de khang', 'vitamin'],
            'qua tang' => ['hop qua', 'cao cap'],
            'do uong' => ['nuoc', 'tra', 'ca phe'],
            'an vat' => ['banh', 'hat', 'snack'],
            'chay' => ['thuan chay', 'vegan'],
            'tra' => ['tea', 'thao moc', 'moc'],
            'ca phe' => ['coffee', 'rang xay'],
            'mat ong' => ['honey', 'ong'],
        ];
    }

    private function extractPhrases(string $text): array
    {
        $parts = $this->tokenize($text);
        $phrases = [];
        $count = count($parts);

        for ($i = 0; $i < $count; $i++) {
            if ($i + 1 < $count) {
                $phrases[] = $parts[$i] . ' ' . $parts[$i + 1];
            }

            if ($i + 2 < $count) {
                $phrases[] = $parts[$i] . ' ' . $parts[$i + 1] . ' ' . $parts[$i + 2];
            }
        }

        return array_values(array_unique($phrases));
    }

    private function tokenize(string $text): array
    {
        $normalized = $this->normalizeText($text);
        $parts = preg_split('/[^a-z0-9]+/u', $normalized) ?: [];
        return array_values(array_filter($parts));
    }

    /**
     * Normalize Vietnamese text to ASCII for comparison
     * This is used for token matching
     */
    private function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $ascii = Str::ascii($text);
        return preg_replace('/\s+/u', ' ', $ascii) ?? $ascii;
    }

    /**
     * Preserve Vietnamese diacritics for exact matching
     * Useful for detecting exact product names without losing Vietnamese marks
     */
    private function normalizeTextPreserveVietnamese(string $text): string
    {
        return mb_strtolower(trim($text));
    }

    /**
     * Check if message contains exact Vietnamese product name (with diacritics)
     */
    private function containsVietnameseProductName(string $message, string $productName): bool
    {
        $msgNorm = $this->normalizeTextPreserveVietnamese($message);
        $prodNorm = $this->normalizeTextPreserveVietnamese($productName);

        // Exact match or substring match
        return str_contains($msgNorm, $prodNorm) ||
            str_contains($message, $productName) ||
            similar_text($msgNorm, $prodNorm) / max(mb_strlen($msgNorm), mb_strlen($prodNorm)) >= 0.85;
    }

    private function analyzeProductRelevance(Product $product, array $tokens, array $filters, ?float $price, int $stock): array
    {
        $name = $this->normalizeText((string) $product->name);
        $description = $this->normalizeText((string) ($product->description ?? ''));
        $category = $this->normalizeText((string) optional($product->category)->name);

        $variantText = $product->variants->map(function ($variant) {
            return implode(' ', array_filter([
                $variant->sku,
                $variant->volume,
                $variant->weight,
                $variant->color,
                $variant->size,
            ]));
        })->implode(' ');
        $variantText = $this->normalizeText($variantText);

        $score = 0;
        $matchedTokens = [];
        $reasons = [];

        // BONUS: Check for exact product name match (highest priority)
        $hasExactNameMatch = false;
        $fullProductName = $this->normalizeText((string) $product->name);
        foreach ($tokens as $token) {
            if ($this->containsWholeWord($fullProductName, $token)) {
                $hasExactNameMatch = true;
                break;
            }
        }

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $matched = false;
            if ($this->containsWholeWord($name, $token)) {
                $score += 10;
                $matched = true;
                $reasons[] = 'Tên sản phẩm khớp "' . $token . '"';
            } elseif (str_contains($name, $token)) {
                $score += 7;
                $matched = true;
                $reasons[] = 'Tên sản phẩm gần khớp "' . $token . '"';
            }

            if ($this->containsWholeWord($category, $token)) {
                $score += 8;
                $matched = true;
                $reasons[] = 'Danh mục khớp "' . $token . '"';
            }

            if ($this->containsWholeWord($description, $token) || str_contains($variantText, $token)) {
                $score += 5;
                $matched = true;
                $reasons[] = 'Mô tả/biến thể có "' . $token . '"';
            }

            if ($matched) {
                $matchedTokens[] = $token;
            }
        }

        if (!empty($filters['category'])) {
            $categoryFilter = $this->normalizeText((string) $filters['category']);
            if ($categoryFilter !== '' && str_contains($category, $categoryFilter)) {
                $score += 12;
                $reasons[] = 'Khớp danh mục mong muốn';
            } elseif ($categoryFilter !== '') {
                $score -= 6;
            }
        }

        if (!empty($filters['price_min']) && $price !== null && $price < (float) $filters['price_min']) {
            $score -= 8;
        }

        if (!empty($filters['price_max']) && $price !== null && $price > (float) $filters['price_max']) {
            $score -= 10;
        }

        if ($stock <= 0) {
            $score -= 5;
        } else {
            $score += min(4, (int) floor($stock / 5));
        }

        if (!empty($tokens) && empty($matchedTokens)) {
            $score -= 14;
        }

        // BONUS: Exact product name match gets extreme priority
        if ($hasExactNameMatch) {
            $score += 50; // Very high boost for exact name match
            $reasons = array_merge(['🎯 Khớp chính xác tên sản phẩm!'], $reasons);
        }

        return [
            'score' => $score,
            'matched_token_count' => count(array_unique($matchedTokens)),
            'reasons' => array_values(array_unique(array_slice($reasons, 0, 3))),
        ];
    }

    private function containsWholeWord(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        return (bool) preg_match('/(?:^|\s)' . preg_quote($needle, '/') . '(?:\s|$)/u', $haystack);
    }

    private function buildSuggestedQuestions(array $filters, array $products): array
    {
        $category = $filters['category'] ?? null;
        $priceMin = $filters['price_min'] ?? null;
        $priceMax = $filters['price_max'] ?? null;
        $keywords = $filters['keywords'] ?? [];
        $needs = $filters['needs'] ?? [];

        $categoryText = $category ? (string) $category : 'sản phẩm OCOP';

        // Xây dựng mô tả khoảng giá từ price_min và price_max
        $priceRangeText = '';
        if ($priceMin && $priceMax) {
            $priceRangeText = 'từ ' . number_format((float) $priceMin, 0, ',', '.') . 'đ đến ' . number_format((float) $priceMax, 0, ',', '.') . 'đ';
        } elseif ($priceMin) {
            $priceRangeText = 'từ ' . number_format((float) $priceMin, 0, ',', '.') . 'đ trở lên';
        } elseif ($priceMax) {
            $priceRangeText = 'dưới ' . number_format((float) $priceMax, 0, ',', '.') . 'đ';
        }

        $questions = [];

        // Q1: Gợi ý lấy làm quà biếu (hoặc phù hợp với category)
        if (!empty($categoryText)) {
            $questions[] = 'Có ' . $categoryText . ' nào phù hợp để làm quà biếu không?';
        }

        // Q2: Gợi ý theo khoảng giá hoặc danh mục
        if (!empty($priceRangeText)) {
            $questions[] = 'Gợi ý giúp mình ' . $categoryText . ' ' . $priceRangeText . '.';
        } elseif (!empty($categoryText)) {
            $questions[] = 'Gợi ý những ' . $categoryText . ' được mua nhiều nhất.';
        }

        // Q3: Gợi ý thông minh dựa trên kết quả tìm kiếm
        if (count($products) > 1) {
            // Nhiều sản phẩm: gợi ý so sánh
            $firstName = (string) ($products[0]['name'] ?? 'sản phẩm này');
            $questions[] = 'So sánh ' . $firstName . ' với những lựa chọn khác giúp mình.';
        } elseif (count($products) === 1) {
            // Chỉ 1 sản phẩm: gợi ý tìm thêm hoặc lọc khác
            if (!empty($keywords)) {
                $keywordText = implode(', ', array_slice($keywords, 0, 2));
                $questions[] = 'Có sản phẩm ' . $keywordText . ' khác không?';
            } else {
                $questions[] = 'Có ' . $categoryText . ' tương tự nào khác không?';
            }
        } else {
            // Không tìm thấy: gợi ý làm rõ nhu cầu
            if (!empty($needs)) {
                $needText = (string) ($needs[0] ?? 'sức khỏe');
                $questions[] = 'Bạn tìm sản phẩm để ' . $needText . ' phải không? Có thể tôi giúp gì thêm?';
            } else {
                $questions[] = 'Bạn có thể mô tả thêm nhu cầu hoặc tìm kiếm khác không?';
            }
        }

        // Lọc bỏ trùng lặp và trả về 3 câu hỏi hàng đầu
        $unique = array_unique(array_filter($questions));
        return array_values(array_slice($unique, 0, 3));
    }
}
