<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\SubscriptionPlan;
use App\Models\FineTuneModel;

class CustomTemplateModels extends Component
{
    public $models;
    public $fine_tunes;
    public $default_model;
    public $template;

    public function __construct($template)
    {
        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $this->models = $models;
        $this->fine_tunes = FineTuneModel::all();
        $this->default_model = auth()->user()->default_model_template;
        $this->template = $template;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.custom-template-models');
    }
}
