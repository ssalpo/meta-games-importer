<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GenerationPrompt;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GenerationPromptController extends Controller
{
    public function index(): View
    {
        $prompts = GenerationPrompt::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('generation-prompts.index', [
            'prompts' => $prompts,
        ]);
    }

    public function create(): View
    {
        return view('generation-prompts.create', [
            'prompt' => new GenerationPrompt([
                'is_active' => true,
                'sort_order' => 100,
                'system_prompt' => $this->defaultUniversalSystemPrompt(),
                'user_prompt_template' => $this->defaultUniversalUserPromptTemplate(),
                'description_template' => '',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        GenerationPrompt::create($this->validatedData($request));

        return redirect()
            ->route('generation-prompts.index')
            ->with('status', 'Промпт создан.');
    }

    public function edit(GenerationPrompt $generationPrompt): View
    {
        return view('generation-prompts.edit', [
            'prompt' => $generationPrompt,
        ]);
    }

    public function update(Request $request, GenerationPrompt $generationPrompt): RedirectResponse
    {
        $generationPrompt->update($this->validatedData($request));

        return redirect()
            ->route('generation-prompts.index')
            ->with('status', 'Промпт обновлен.');
    }

    public function destroy(GenerationPrompt $generationPrompt): RedirectResponse
    {
        $generationPrompt->delete();

        return redirect()
            ->route('generation-prompts.index')
            ->with('status', 'Промпт удален.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'system_prompt' => ['required', 'string'],
            'user_prompt_template' => ['required', 'string'],
            'description_template' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function defaultUniversalSystemPrompt(): string
    {
        return <<<'PROMPT'
Ты профессиональный маркетинговый редактор для каталога цифровых товаров и услуг.
Нужно по названию продукта создать русскую и английскую карточку товара.

Правила:
- Верни только валидный json.
- Структура json строго такая: {"title_ru":"...","description_ru":"...","title_en":"...","description_en":"..."}.
- Не добавляй markdown, комментарии или пояснения вне json.
- Описание RU делай на русском языке.
- Описание EN делай на английском языке, как естественную локализацию RU-версии.
- Описание должно быть продающим и структурированным.
- Если передан шаблон описания, строго следуй его структуре.
- Не выдумывай точные факты, если они неизвестны.
- Не упоминай DeepSeek и процесс генерации.
PROMPT;
    }

    private function defaultUniversalUserPromptTemplate(): string
    {
        return <<<'PROMPT'
Сгенерируй карточку товара для продукта: {product_title}

Дополнительные инструкции пользователя:
{instructions}

Шаблон описания:
{description_template}
PROMPT;
    }
}
