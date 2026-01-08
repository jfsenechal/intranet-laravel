<?php

namespace AcMarche\Hrm;

use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Application;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\ContractNature;
use AcMarche\Hrm\Models\ContractType;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\HealthInsurance;
use AcMarche\Hrm\Models\HrDocument;
use AcMarche\Hrm\Models\HrNotification;
use AcMarche\Hrm\Models\Internship;
use AcMarche\Hrm\Models\JobFunction;
use AcMarche\Hrm\Models\NotificationUser;
use AcMarche\Hrm\Models\Operator;
use AcMarche\Hrm\Models\PayScale;
use AcMarche\Hrm\Models\Prerequisite;
use AcMarche\Hrm\Models\PublicHoliday;
use AcMarche\Hrm\Models\Service;
use AcMarche\Hrm\Models\Sms;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Models\Training;
use AcMarche\Hrm\Models\Valorization;
use AcMarche\Hrm\Policies\AbsencePolicy;
use AcMarche\Hrm\Policies\ApplicationPolicy;
use AcMarche\Hrm\Policies\ContractNaturePolicy;
use AcMarche\Hrm\Policies\ContractPolicy;
use AcMarche\Hrm\Policies\ContractTypePolicy;
use AcMarche\Hrm\Policies\DeadlinePolicy;
use AcMarche\Hrm\Policies\DiplomaPolicy;
use AcMarche\Hrm\Policies\DirectionPolicy;
use AcMarche\Hrm\Policies\EmployeePolicy;
use AcMarche\Hrm\Policies\EmployerPolicy;
use AcMarche\Hrm\Policies\EvaluationPolicy;
use AcMarche\Hrm\Policies\HealthInsurancePolicy;
use AcMarche\Hrm\Policies\HrDocumentPolicy;
use AcMarche\Hrm\Policies\HrNotificationPolicy;
use AcMarche\Hrm\Policies\InternshipPolicy;
use AcMarche\Hrm\Policies\JobFunctionPolicy;
use AcMarche\Hrm\Policies\NotificationUserPolicy;
use AcMarche\Hrm\Policies\OperatorPolicy;
use AcMarche\Hrm\Policies\PayScalePolicy;
use AcMarche\Hrm\Policies\PrerequisitePolicy;
use AcMarche\Hrm\Policies\PublicHolidayPolicy;
use AcMarche\Hrm\Policies\ServicePolicy;
use AcMarche\Hrm\Policies\SmsPolicy;
use AcMarche\Hrm\Policies\TeleworkPolicy;
use AcMarche\Hrm\Policies\TrainingPolicy;
use AcMarche\Hrm\Policies\ValorizationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class HrmServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Absence::class => AbsencePolicy::class,
        Application::class => ApplicationPolicy::class,
        Contract::class => ContractPolicy::class,
        ContractNature::class => ContractNaturePolicy::class,
        ContractType::class => ContractTypePolicy::class,
        Deadline::class => DeadlinePolicy::class,
        Diploma::class => DiplomaPolicy::class,
        Direction::class => DirectionPolicy::class,
        Employee::class => EmployeePolicy::class,
        Employer::class => EmployerPolicy::class,
        Evaluation::class => EvaluationPolicy::class,
        HealthInsurance::class => HealthInsurancePolicy::class,
        HrDocument::class => HrDocumentPolicy::class,
        HrNotification::class => HrNotificationPolicy::class,
        Internship::class => InternshipPolicy::class,
        JobFunction::class => JobFunctionPolicy::class,
        NotificationUser::class => NotificationUserPolicy::class,
        Operator::class => OperatorPolicy::class,
        PayScale::class => PayScalePolicy::class,
        Prerequisite::class => PrerequisitePolicy::class,
        PublicHoliday::class => PublicHolidayPolicy::class,
        Service::class => ServicePolicy::class,
        Sms::class => SmsPolicy::class,
        Telework::class => TeleworkPolicy::class,
        Training::class => TrainingPolicy::class,
        Valorization::class => ValorizationPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge HRM config
        $this->mergeConfigFrom(
            __DIR__.'/../config/hrm.php',
            'hrm'
        );

        // Register database connection from module config
        $this->registerDatabaseConnection();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register policies
       // $this->registerPolicies(); todo ??

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hrm');

        // Load routes
        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/hrm.php' => config_path('hrm.php'),
        ], 'hrm-config');

        // Publish database config
        $this->publishes([
            __DIR__.'/../config/database.php' => config_path('hrm-database.php'),
        ], 'hrm-database-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'hrm-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/hrm'),
        ], 'hrm-views');
    }

    /**
     * Register the module's database connection.
     */
    protected function registerDatabaseConnection(): void
    {
        $connections = require __DIR__.'/../config/database.php';

        foreach ($connections['connections'] ?? [] as $name => $config) {
            config(['database.connections.'.$name => $config]);
        }
    }

    /**
     * Register the policies for the module.
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
