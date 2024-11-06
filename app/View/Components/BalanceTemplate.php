<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BalanceTemplate extends Component
{
    public $model;
    public $balance;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $default = auth()->user()->default_model_template;

        switch ($default) {
            case 'gpt-3.5-turbo-0125':
                $this->model = 'GPT 3.5 Turbo';
                $this->balance = (auth()->user()->gpt_3_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableWords();
                break;
            case 'gpt-4':
                $this->model = 'GPT 4';
                $this->balance = (auth()->user()->gpt_4_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4Words();
                break;
            case 'gpt-4o':
                $this->model = 'GPT 4o';
                $this->balance = (auth()->user()->gpt_4o_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4oWords();
                break;
            case 'gpt-4o-mini':
                $this->model = 'GPT 4o mini';
                $this->balance = (auth()->user()->gpt_4o_mini_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4oMiniWords();
                break;
            case 'gpt-4-0125-preview':
                $this->model = 'GPT 4 Turbo';
                $this->balance = (auth()->user()->gpt_4_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4TWords();
                break;            
            case 'gpt-4-turbo-2024-04-09':
                $this->model = 'GPT 4 Turbo Vision';
                $this->balance = (auth()->user()->gpt_4_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4TWords();
                break;
            case 'claude-3-opus-20240229':
                $this->model = 'Claude 3 Opus';
                $this->balance = (auth()->user()->claude_3_opus_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeOpusWords();
                break;
            case 'claude-3-5-sonnet-20240620':
                $this->model = 'Claude 3.5 Sonnet';
                $this->balance = (auth()->user()->claude_3_sonnet_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeSonnetWords();
                break;
            case 'claude-3-haiku-20240307':
                $this->model = 'Claude 3 Haiku';
                $this->balance = (auth()->user()->claude_3_haiku_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeHaikuWords();
                break;
            case 'gemini_pro':
                $this->model = 'Gemini Pro';
                $this->balance = (auth()->user()->gemini_pro_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGeminiProWords();
                break;
            default:
                $this->model = 'Fine Tune';
                $this->balance = (auth()->user()->fine_tune_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableFineTuneWords();
                break;
        }
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.balance-template');
    }
}
