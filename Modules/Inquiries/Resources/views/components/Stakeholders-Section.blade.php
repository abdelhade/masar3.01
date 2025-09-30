<!-- Stakeholders Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-dark">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    الأطراف المعنية
                </h6>
                <small class="d-block mt-1">تحديد جميع الأطراف المشاركة في المشروع</small>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname" wire-model="clientId"
                                label="العميل" placeholder="ابحث عن العميل أو أضف جديد..." :where="[
                                    'type' => [
                                        \App\Enums\ClientType::Person->value,
                                        \App\Enums\ClientType::Company->value,
                                    ],
                                ]"
                                :additional-data="['type' => \App\Enums\ClientType::Person->value]" :key="'client-select'" />
                        </div>
                    </div>

                    <!-- مثال 3: المقاول الرئيسي -->
                    <div class="col-md-3 mb-3">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-hard-hat fa-2x text-warning"></i>
                            </div>
                            <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                wire-model="mainContractorId" label="المقاول الرئيسي"
                                placeholder="ابحث أو أضف مقاول جديد..." :where="[
                                    'type' => \App\Enums\ClientType::MainContractor->value,
                                ]" :additional-data="[
                                    'type' => \App\Enums\ClientType::MainContractor->value,
                                ]"
                                :key="'contractor-select'" />
                        </div>
                    </div>

                    <!-- مثال 4: الاستشاري -->
                    <div class="col-md-3 mb-3">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                            </div>
                            <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                wire-model="consultantId" label="الاستشاري" :where="[
                                    'type' => \App\Enums\ClientType::Consultant->value,
                                ]" :additional-data="[
                                    'type' => \App\Enums\ClientType::Consultant->value,
                                ]"
                                :key="'consultant-select'" />
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-crown fa-2x text-success"></i>
                            </div>
                            <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname" wire-model="ownerId"
                                label="المالك" placeholder="ابحث عن المالك أو أضف جديد..." :where="['type' => \App\Enums\ClientType::Owner->value]"
                                :additional-data="['type' => \App\Enums\ClientType::Owner->value]" :key="'owner-select'" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
