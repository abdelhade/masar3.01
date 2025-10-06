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

                    <!-- العميل -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <label class="form-label fw-bold">العميل</label>

                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="clientId" placeholder="ابحث عن العميل أو أضف جديد..."
                                        :where="[
                                            'type' => [
                                                \App\Enums\ClientType::Person->value,
                                                \App\Enums\ClientType::Company->value,
                                            ],
                                        ]" :selected-id="$clientId" :additional-data="['type' => \App\Enums\ClientType::Person->value]" :key="'client-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-primary"
                                    wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Person->value }} })"
                                    title="إضافة عميل جديد">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            @if ($clientId)
                                @php
                                    $client = \App\Models\Client::find($clientId);
                                @endphp
                                @if ($client)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block"><strong>الاسم:</strong> {{ $client->cname }}</small>
                                            @if ($client->phone)
                                                <small class="d-block"><strong>الهاتف:</strong>
                                                    {{ $client->phone }}</small>
                                            @endif
                                            @if ($client->email)
                                                <small class="d-block"><strong>البريد:</strong>
                                                    {{ $client->email }}</small>
                                            @endif
                                            @if ($client->address)
                                                <small class="d-block"><strong>العنوان:</strong>
                                                    {{ $client->address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- المقاول الرئيسي -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-hard-hat fa-2x text-warning"></i>
                            </div>
                            <label class="form-label fw-bold">المقاول الرئيسي</label>

                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="mainContractorId" :selected-id="$mainContractorId"
                                        placeholder="ابحث أو أضف مقاول جديد..." :where="[
                                            'type' => \App\Enums\ClientType::MainContractor->value,
                                        ]" :additional-data="[
                                            'type' => \App\Enums\ClientType::MainContractor->value,
                                        ]"
                                        :key="'contractor-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-warning"
                                    wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::MainContractor->value }} })"
                                    title="إضافة مقاول جديد">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            @if ($mainContractorId)
                                @php
                                    $contractor = \App\Models\Client::find($mainContractorId);
                                @endphp
                                @if ($contractor)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block"><strong>الاسم:</strong>
                                                {{ $contractor->cname }}</small>
                                            @if ($contractor->phone)
                                                <small class="d-block"><strong>الهاتف:</strong>
                                                    {{ $contractor->phone }}</small>
                                            @endif
                                            @if ($contractor->email)
                                                <small class="d-block"><strong>البريد:</strong>
                                                    {{ $contractor->email }}</small>
                                            @endif
                                            @if ($contractor->address)
                                                <small class="d-block"><strong>العنوان:</strong>
                                                    {{ $contractor->address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- الاستشاري -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-graduate fa-2x text-info"></i>
                            </div>
                            <label class="form-label fw-bold">الاستشاري</label>

                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="consultantId" :selected-id="$consultantId" :where="[
                                            'type' => \App\Enums\ClientType::Consultant->value,
                                        ]"
                                        :additional-data="[
                                            'type' => \App\Enums\ClientType::Consultant->value,
                                        ]" :key="'consultant-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-info"
                                    wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Consultant->value }} })"
                                    title="إضافة استشاري جديد">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            @if ($consultantId)
                                @php
                                    $consultant = \App\Models\Client::find($consultantId);
                                @endphp
                                @if ($consultant)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block"><strong>الاسم:</strong>
                                                {{ $consultant->cname }}</small>
                                            @if ($consultant->phone)
                                                <small class="d-block"><strong>الهاتف:</strong>
                                                    {{ $consultant->phone }}</small>
                                            @endif
                                            @if ($consultant->email)
                                                <small class="d-block"><strong>البريد:</strong>
                                                    {{ $consultant->email }}</small>
                                            @endif
                                            @if ($consultant->address)
                                                <small class="d-block"><strong>العنوان:</strong>
                                                    {{ $consultant->address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- المالك -->
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-crown fa-2x text-success"></i>
                            </div>
                            <label class="form-label fw-bold">المالك</label>

                            <div class="d-flex gap-2 align-items-center">
                                <div class="flex-grow-1">
                                    <livewire:app::searchable-select :model="App\Models\Client::class" label-field="cname"
                                        wire-model="ownerId" placeholder="ابحث عن المالك أو أضف جديد..."
                                        :where="['type' => \App\Enums\ClientType::Owner->value]" :selected-id="$ownerId" :additional-data="['type' => \App\Enums\ClientType::Owner->value]" :key="'owner-select'" />
                                </div>
                                <button type="button" class="btn btn-sm btn-success"
                                    wire:click="$dispatch('openClientModal', { type: {{ \App\Enums\ClientType::Owner->value }} })"
                                    title="إضافة مالك جديد">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            @if ($ownerId)
                                @php
                                    $owner = \App\Models\Client::find($ownerId);
                                @endphp
                                @if ($owner)
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body p-2 text-start">
                                            <small class="d-block"><strong>الاسم:</strong> {{ $owner->cname }}</small>
                                            @if ($owner->phone)
                                                <small class="d-block"><strong>الهاتف:</strong>
                                                    {{ $owner->phone }}</small>
                                            @endif
                                            @if ($owner->email)
                                                <small class="d-block"><strong>البريد:</strong>
                                                    {{ $owner->email }}</small>
                                            @endif
                                            @if ($owner->address)
                                                <small class="d-block"><strong>العنوان:</strong>
                                                    {{ $owner->address }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Client -->

@include('inquiries::components.addClientModal')

