@extends('layouts.admin')

@section('title', 'Редактировать вакансию')
@section('header', 'Редактировать: ' . $vacancy->title)

@push('styles')
<style>
/* Page Header */
.page-intro {
    background: linear-gradient(135deg, var(--panel) 0%, var(--grid) 100%);
    border: 1px solid var(--br);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.page-intro__icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-radius: 16px;
    font-size: 28px;
    flex-shrink: 0;
}

.page-intro__content h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--fg-1);
    margin: 0 0 6px 0;
}

.page-intro__content p {
    font-size: 14px;
    color: var(--fg-3);
    margin: 0;
    line-height: 1.5;
}

/* Form Sections */
.form-section {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 16px;
    margin-bottom: 24px;
}

.form-section__header {
    background: var(--grid);
    border-bottom: 1px solid var(--br);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-section__icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 16px;
}

.form-section__icon.red { background: rgba(214, 0, 28, 0.1); color: var(--accent); }
.form-section__icon.blue { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
.form-section__icon.green { background: rgba(34, 197, 94, 0.1); color: #16a34a; }
.form-section__icon.purple { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }
.form-section__icon.yellow { background: rgba(245, 158, 11, 0.1); color: #d97706; }

.form-section__title {
    font-size: 15px;
    font-weight: 700;
    color: var(--fg-1);
    margin: 0;
}

.form-section__body {
    padding: 24px;
}

/* Form Controls */
.form-label {
    font-weight: 600;
    font-size: 13px;
    color: var(--fg-2);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-label .required {
    color: var(--accent);
    font-weight: 700;
}

.form-control, .form-select {
    border: 2px solid var(--br);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    background: var(--panel);
    color: var(--fg-1);
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(214, 0, 28, 0.1);
    outline: none;
}

.form-control::placeholder {
    color: var(--fg-3);
    font-weight: 400;
}

textarea.form-control {
    resize: vertical;
    min-height: 150px;
}

.form-text {
    font-size: 12px;
    color: var(--fg-3);
    margin-top: 6px;
}

/* Input Groups */
.input-with-icon {
    position: relative;
}

.input-with-icon .form-control {
    padding-left: 44px;
}

.input-with-icon__icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fg-3);
    font-size: 16px;
}

/* Skills Input */
.skills-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
    min-height: 20px;
}

.skills-tags:empty::before {
    content: 'Навыки не выбраны';
    color: var(--fg-3);
    font-size: 13px;
    font-style: italic;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: default;
    transition: all 0.2s ease;
}

.skill-tag.must-have {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.skill-tag.nice-to-have {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.skill-tag__remove {
    cursor: pointer;
    opacity: 0.6;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(0,0,0,0.08);
    font-size: 11px;
}

.skill-tag__remove:hover {
    opacity: 1;
    background: rgba(0,0,0,0.18);
}

/* Skills Modal */
.skills-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.skills-modal-overlay.show { display: flex; }
.skills-modal {
    background: var(--panel, #ffffff);
    border-radius: 16px;
    width: 100%;
    max-width: 640px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    animation: skillsModalIn 0.2s ease;
}
@keyframes skillsModalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.skills-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--br, #e9edf5);
}
.skills-modal__title {
    font-size: 16px;
    font-weight: 700;
    color: var(--fg-1, #0f1526);
    margin: 0;
}
.skills-modal__close {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: var(--grid, #eef1f7);
    color: var(--fg-3, #7c87a5);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.15s;
}
.skills-modal__close:hover { background: #fee2e2; color: #dc2626; }
.skills-modal__search {
    padding: 12px 24px;
    border-bottom: 1px solid var(--br, #e9edf5);
}
.skills-modal__search input {
    width: 100%;
    padding: 10px 16px;
    border: 2px solid var(--br, #e9edf5);
    border-radius: 10px;
    font-size: 14px;
    background: var(--panel, #ffffff);
    color: var(--fg-1, #0f1526);
    outline: none;
    transition: border-color 0.15s;
}
.skills-modal__search input:focus { border-color: var(--accent, #E52716); }
.skills-modal__search input::placeholder { color: var(--fg-3, #7c87a5); }
.skills-modal__body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 24px;
}
.skills-modal__group-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--fg-3, #7c87a5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 16px 0 8px 0;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--br, #e9edf5);
}
.skills-modal__group-title:first-child { margin-top: 0; }
.skills-modal__options {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 4px;
}
.skill-option {
    display: inline-flex;
    align-items: center;
    padding: 7px 14px;
    background: var(--grid, #eef1f7);
    border: 1px solid var(--br, #e9edf5);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--fg-2, #2d3754);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.skill-option:hover {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
    color: #2563eb;
}
.skill-option.selected {
    background: rgba(34, 197, 94, 0.12);
    border-color: rgba(34, 197, 94, 0.4);
    color: #16a34a;
}
.skill-option.selected::after {
    content: '\2713';
    margin-left: 6px;
    font-size: 11px;
}
.skills-modal__empty {
    color: var(--fg-3, #7c87a5);
    font-size: 13px;
    text-align: center;
    padding: 30px 20px;
}
.skills-modal__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-top: 1px solid var(--br, #e9edf5);
}
.skills-modal__counter {
    font-size: 13px;
    color: var(--fg-3, #7c87a5);
}
.skills-modal__counter strong { color: var(--fg-1, #0f1526); }
.skills-modal__done {
    padding: 8px 24px;
    background: var(--accent, #E52716);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.skills-modal__done:hover { opacity: 0.9; }
.skills-open-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: 2px dashed var(--br, #e9edf5);
    border-radius: 10px;
    background: none;
    color: var(--fg-3, #7c87a5);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.15s;
    width: 100%;
}
.skills-open-btn:hover {
    border-color: var(--accent, #E52716);
    color: var(--accent, #E52716);
    background: rgba(229, 39, 22, 0.03);
}

/* Checkbox Switch */
.form-switch-custom {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: var(--grid);
    border: 1px solid var(--br);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.form-switch-custom:hover {
    border-color: var(--accent);
}

.form-switch-custom input {
    display: none;
}

.form-switch-custom__toggle {
    width: 48px;
    height: 28px;
    background: var(--br);
    border-radius: 14px;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.form-switch-custom__toggle::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 4px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.form-switch-custom input:checked + .form-switch-custom__toggle {
    background: var(--good);
}

.form-switch-custom input:checked + .form-switch-custom__toggle::after {
    left: 24px;
}

.form-switch-custom__label {
    flex: 1;
}

.form-switch-custom__title {
    font-weight: 600;
    font-size: 14px;
    color: var(--fg-1);
    margin-bottom: 2px;
}

.form-switch-custom__desc {
    font-size: 12px;
    color: var(--fg-3);
}

/* Stats Card */
.stats-card {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}

.stats-card__header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    border-bottom: 1px solid var(--br);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-card__header i {
    color: #2563eb;
    font-size: 18px;
}

.stats-card__header span {
    font-weight: 700;
    font-size: 14px;
    color: var(--fg-1);
}

.stats-card__body {
    padding: 20px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--br);
}

.stat-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.stat-row:first-child {
    padding-top: 0;
}

.stat-row__label {
    font-size: 13px;
    color: var(--fg-3);
    font-weight: 500;
}

.stat-row__value {
    font-size: 14px;
    font-weight: 700;
    color: var(--fg-1);
}

/* Danger Card */
.danger-card {
    background: var(--panel);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 16px;
    overflow: hidden;
}

.danger-card__header {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.danger-card__header i {
    color: #dc2626;
    font-size: 18px;
}

.danger-card__header span {
    font-weight: 700;
    font-size: 14px;
    color: #dc2626;
}

.danger-card__body {
    padding: 20px;
}

.danger-card__body p {
    font-size: 13px;
    color: var(--fg-3);
    margin-bottom: 16px;
}

.btn-danger-custom {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 20px;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-danger-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* Action Buttons */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: var(--grid);
    border: 1px solid var(--br);
    border-radius: 16px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--panel);
    color: var(--fg-2);
    border: 2px solid var(--br);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-back:hover {
    border-color: var(--fg-3);
    color: var(--fg-1);
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, var(--accent) 0%, #b8001a 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(214, 0, 28, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(214, 0, 28, 0.4);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.status-badge.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}
</style>
@endpush

@section('content')
<!-- Page Intro -->
<div class="page-intro">
    <div class="page-intro__icon">
        <i class="bi bi-pencil-square"></i>
    </div>
    <div class="page-intro__content">
        <h2>Редактирование вакансии</h2>
        <p>Измените параметры вакансии. После сохранения AI продолжит анализировать кандидатов с учётом новых требований.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.vacancies.update', $vacancy) }}" method="POST" id="vacancyForm">
            @csrf
            @method('PUT')

            <!-- Basic Info Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon red">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <h3 class="form-section__title">Основная информация</h3>
                </div>
                <div class="form-section__body">
                    <div class="mb-4">
                        <label for="title" class="form-label">
                            Название вакансии <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <span class="input-with-icon__icon"><i class="bi bi-briefcase"></i></span>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $vacancy->title) }}"
                                   placeholder="Например: Senior Java Developer"
                                   required>
                        </div>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">
                            Описание вакансии <span class="required">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="6"
                                  placeholder="Опишите обязанности, требования, условия работы..."
                                  required>{{ old('description', $vacancy->description) }}</textarea>
                        <div class="form-text">Подробное описание поможет AI лучше оценить соответствие кандидатов</div>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Employment Details Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3 class="form-section__title">Условия работы</h3>
                </div>
                <div class="form-section__body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="employment_type" class="form-label">
                                Тип занятости <span class="required">*</span>
                            </label>
                            <select class="form-select @error('employment_type') is-invalid @enderror"
                                    id="employment_type"
                                    name="employment_type"
                                    required>
                                @foreach($employmentTypes as $type)
                                    <option value="{{ $type->value }}"
                                            {{ old('employment_type', $vacancy->employment_type->value) == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label">
                                <i class="bi bi-geo-alt me-1"></i> Локация
                            </label>
                            <input type="text"
                                   class="form-control @error('location') is-invalid @enderror"
                                   id="location"
                                   name="location"
                                   value="{{ old('location', $vacancy->location) }}"
                                   placeholder="Ташкент">
                            @error('location')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="min_experience_years" class="form-label">
                                <i class="bi bi-clock-history me-1"></i> Мин. опыт (лет)
                            </label>
                            <input type="number"
                                   class="form-control @error('min_experience_years') is-invalid @enderror"
                                   id="min_experience_years"
                                   name="min_experience_years"
                                   value="{{ old('min_experience_years', $vacancy->min_experience_years) }}"
                                   min="0"
                                   max="50"
                                   step="0.5"
                                   placeholder="0">
                            @error('min_experience_years')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="salary_min" class="form-label">
                                <i class="bi bi-cash me-1"></i> Зарплата от
                            </label>
                            <input type="number"
                                   class="form-control @error('salary_min') is-invalid @enderror"
                                   id="salary_min"
                                   name="salary_min"
                                   value="{{ old('salary_min', $vacancy->salary_min) }}"
                                   min="0"
                                   placeholder="5 000 000">
                            @error('salary_min')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="salary_max" class="form-label">
                                <i class="bi bi-cash-stack me-1"></i> Зарплата до
                            </label>
                            <input type="number"
                                   class="form-control @error('salary_max') is-invalid @enderror"
                                   id="salary_max"
                                   name="salary_max"
                                   value="{{ old('salary_max', $vacancy->salary_max) }}"
                                   min="0"
                                   placeholder="15 000 000">
                            @error('salary_max')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-text" style="margin-top: -12px;">Зарплата указывается в сумах</div>
                </div>
            </div>

            <!-- Skills Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon green">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h3 class="form-section__title">Требуемые навыки</h3>
                </div>
                <div class="form-section__body">
                    <!-- Must-have Skills -->
                    <div class="mb-4">
                        <label class="form-label">
                            <span style="color: #dc2626;"><i class="bi bi-check-circle-fill me-1"></i></span>
                            Обязательные навыки (must-have)
                        </label>
                        <div class="skills-tags" id="mustHaveTags"></div>
                        <button type="button" class="skills-open-btn" id="mustHaveOpenBtn">
                            <i class="bi bi-plus-circle"></i> Выбрать навыки
                        </button>
                        <input type="hidden" id="must_have_skills" name="must_have_skills_input"
                               value="{{ old('must_have_skills_input', implode(', ', $vacancy->must_have_skills ?? [])) }}">
                        <div class="form-text">Кандидат должен владеть этими навыками. Влияют на match score.</div>
                        @error('must_have_skills')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nice-to-have Skills -->
                    <div class="mb-0">
                        <label class="form-label">
                            <span style="color: #d97706;"><i class="bi bi-star-fill me-1"></i></span>
                            Желательные навыки (nice-to-have)
                        </label>
                        <div class="skills-tags" id="niceToHaveTags"></div>
                        <button type="button" class="skills-open-btn" id="niceToHaveOpenBtn">
                            <i class="bi bi-plus-circle"></i> Выбрать навыки
                        </button>
                        <input type="hidden" id="nice_to_have_skills" name="nice_to_have_skills_input"
                               value="{{ old('nice_to_have_skills_input', implode(', ', $vacancy->nice_to_have_skills ?? [])) }}">
                        <div class="form-text">Желательные, но не обязательные навыки</div>
                        @error('nice_to_have_skills')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Publication Section -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon purple">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <h3 class="form-section__title">Публикация</h3>
                </div>
                <div class="form-section__body">
                    <label class="form-switch-custom">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $vacancy->is_active) ? 'checked' : '' }}>
                        <span class="form-switch-custom__toggle"></span>
                        <span class="form-switch-custom__label">
                            <span class="form-switch-custom__title">Вакансия активна</span>
                            <span class="form-switch-custom__desc">Вакансия видна кандидатам на сайте</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.vacancies.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    <span>Назад к списку</span>
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-lg"></i>
                    <span>Сохранить изменения</span>
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Stats Card -->
        <div class="stats-card">
            <div class="stats-card__header">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Статистика вакансии</span>
            </div>
            <div class="stats-card__body">
                <div class="stat-row">
                    <span class="stat-row__label">Статус</span>
                    <span class="status-badge {{ $vacancy->is_active ? 'active' : 'inactive' }}">
                        <i class="bi bi-{{ $vacancy->is_active ? 'check-circle-fill' : 'pause-circle-fill' }}"></i>
                        {{ $vacancy->is_active ? 'Активна' : 'Неактивна' }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Всего заявок</span>
                    <span class="stat-row__value">{{ $vacancy->applications_count }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Создано</span>
                    <span class="stat-row__value">{{ $vacancy->created_at->format('d.m.Y') }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-row__label">Автор</span>
                    <span class="stat-row__value">{{ $vacancy->creator?->name ?? 'Система' }}</span>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="danger-card">
            <div class="danger-card__header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Опасная зона</span>
            </div>
            <div class="danger-card__body">
                @if($vacancy->applications()->exists())
                    <p style="margin: 0;">
                        <i class="bi bi-lock-fill me-1"></i>
                        Нельзя удалить вакансию, на которую есть заявки ({{ $vacancy->applications_count }}).
                    </p>
                @else
                    <p>Удаление вакансии необратимо. Все данные будут потеряны.</p>
                    <form action="{{ route('admin.vacancies.destroy', $vacancy) }}" method="POST"
                          onsubmit="return confirm('Вы уверены, что хотите удалить эту вакансию? Это действие нельзя отменить.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger-custom">
                            <i class="bi bi-trash-fill"></i>
                            <span>Удалить вакансию</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- Skills Modal -->
<div class="skills-modal-overlay" id="skillsModalOverlay">
    <div class="skills-modal">
        <div class="skills-modal__header">
            <h3 class="skills-modal__title" id="skillsModalTitle">Выбор навыков</h3>
            <button type="button" class="skills-modal__close" id="skillsModalClose"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="skills-modal__search">
            <input type="text" id="skillsModalSearch" placeholder="Поиск навыков... (Enter — добавить свой)" autocomplete="off">
        </div>
        <div class="skills-modal__body" id="skillsModalBody"></div>
        <div class="skills-modal__footer">
            <span class="skills-modal__counter">Выбрано: <strong id="skillsModalCount">0</strong></span>
            <button type="button" class="skills-modal__done" id="skillsModalDone">Готово</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const skillGroups = {
        'Языки программирования': ['JavaScript', 'TypeScript', 'Python', 'Java', 'PHP', 'C#', 'C++', 'Go', 'Rust', 'Ruby', 'Swift', 'Kotlin'],
        'Frontend': ['React', 'Vue.js', 'Angular', 'Next.js', 'Nuxt.js', 'HTML5', 'CSS3', 'SASS', 'LESS', 'Tailwind CSS', 'Bootstrap'],
        'Backend': ['Node.js', 'Express.js', 'Django', 'Flask', 'Laravel', 'Spring Boot', 'ASP.NET', 'FastAPI', 'NestJS', 'Ruby on Rails'],
        'Базы данных': ['MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Elasticsearch', 'Oracle', 'SQL Server', 'SQLite', 'Cassandra', 'SQL'],
        'DevOps и облако': ['Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'CI/CD', 'Jenkins', 'GitLab CI', 'GitHub Actions', 'Terraform', 'Ansible'],
        'Инструменты': ['Git', 'Linux', 'Nginx', 'REST API', 'GraphQL', 'Microservices', 'RabbitMQ', 'Kafka', 'WebSocket'],
        'Мобильная разработка': ['React Native', 'Flutter', 'iOS', 'Android', 'SwiftUI', 'Jetpack Compose'],
        'Тестирование': ['Jest', 'Mocha', 'Cypress', 'Selenium', 'JUnit', 'PHPUnit', 'pytest'],
        'Дизайн и аналитика': ['Figma', 'Adobe XD', 'Photoshop', 'Google Analytics', 'Power BI', 'Tableau'],
        'Языки': ['Английский язык', 'Русский язык', 'Узбекский язык'],
        'Управление': ['Agile', 'Scrum', 'Kanban', 'Jira', 'Confluence', 'Team Lead', 'Project Management'],
        'Финансы': ['1C', 'SAP', 'Финансовый анализ', 'Бухгалтерский учёт', 'Excel', 'VBA'],
    };
    const allSkills = Object.values(skillGroups).flat();

    // State for both skill sets
    const state = {
        must_have_skills: [],
        nice_to_have_skills: [],
    };

    // Modal elements
    const overlay = document.getElementById('skillsModalOverlay');
    const modalBody = document.getElementById('skillsModalBody');
    const modalSearch = document.getElementById('skillsModalSearch');
    const modalTitle = document.getElementById('skillsModalTitle');
    const modalCount = document.getElementById('skillsModalCount');
    let activeKey = null; // 'must_have_skills' or 'nice_to_have_skills'

    // Parse initial values
    ['must_have_skills', 'nice_to_have_skills'].forEach(key => {
        const val = document.getElementById(key).value.trim();
        if (val) state[key] = val.split(',').map(s => s.trim()).filter(s => s);
    });

    function getOtherKey(key) {
        return key === 'must_have_skills' ? 'nice_to_have_skills' : 'must_have_skills';
    }

    function renderTags(key) {
        const container = document.getElementById(key === 'must_have_skills' ? 'mustHaveTags' : 'niceToHaveTags');
        const tagClass = key === 'must_have_skills' ? 'must-have' : 'nice-to-have';
        container.innerHTML = state[key].map(skill => `
            <span class="skill-tag ${tagClass}">
                ${skill}
                <span class="skill-tag__remove" data-key="${key}" data-skill="${skill}">
                    <i class="bi bi-x"></i>
                </span>
            </span>
        `).join('');
        container.querySelectorAll('.skill-tag__remove').forEach(btn => {
            btn.addEventListener('click', function() {
                state[this.dataset.key] = state[this.dataset.key].filter(s => s !== this.dataset.skill);
                syncHidden(this.dataset.key);
                renderTags(this.dataset.key);
            });
        });
    }

    function syncHidden(key) {
        document.getElementById(key).value = state[key].join(', ');
    }

    function openModal(key, title) {
        activeKey = key;
        modalTitle.textContent = title;
        modalSearch.value = '';
        renderModalOptions('');
        overlay.classList.add('show');
        setTimeout(() => modalSearch.focus(), 100);
    }

    function closeModal() {
        overlay.classList.remove('show');
        activeKey = null;
    }

    function renderModalOptions(filter) {
        const filterLower = filter.toLowerCase();
        const excluded = state[getOtherKey(activeKey)];
        const selected = state[activeKey];
        let html = '';

        if (filter.trim()) {
            const filtered = allSkills.filter(s =>
                s.toLowerCase().includes(filterLower) && !excluded.includes(s)
            );
            if (filtered.length === 0) {
                html = `<div class="skills-modal__options"><div class="skill-option" data-skill="${filter}"><i class="bi bi-plus-circle me-1"></i> Добавить "${filter}"</div></div>`;
            } else {
                html = '<div class="skills-modal__options">' +
                    filtered.map(s => `<div class="skill-option ${selected.includes(s) ? 'selected' : ''}" data-skill="${s}">${s}</div>`).join('') +
                    '</div>';
            }
        } else {
            for (const [group, groupSkills] of Object.entries(skillGroups)) {
                const available = groupSkills.filter(s => !excluded.includes(s));
                if (available.length === 0) continue;
                html += `<div class="skills-modal__group-title">${group}</div>`;
                html += '<div class="skills-modal__options">' +
                    available.map(s => `<div class="skill-option ${selected.includes(s) ? 'selected' : ''}" data-skill="${s}">${s}</div>`).join('') +
                    '</div>';
            }
        }

        if (!html) html = '<div class="skills-modal__empty">Ничего не найдено</div>';
        modalBody.innerHTML = html;
        modalCount.textContent = selected.length;

        modalBody.querySelectorAll('.skill-option').forEach(opt => {
            opt.addEventListener('click', function() {
                const skill = this.dataset.skill;
                if (selected.includes(skill)) {
                    state[activeKey] = state[activeKey].filter(s => s !== skill);
                    this.classList.remove('selected');
                } else {
                    state[activeKey].push(skill);
                    this.classList.add('selected');
                }
                modalCount.textContent = state[activeKey].length;
                syncHidden(activeKey);
                renderTags(activeKey);
            });
        });
    }

    // Event listeners
    document.getElementById('mustHaveOpenBtn').addEventListener('click', () =>
        openModal('must_have_skills', 'Обязательные навыки (must-have)')
    );
    document.getElementById('niceToHaveOpenBtn').addEventListener('click', () =>
        openModal('nice_to_have_skills', 'Желательные навыки (nice-to-have)')
    );
    document.getElementById('skillsModalClose').addEventListener('click', closeModal);
    document.getElementById('skillsModalDone').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });
    modalSearch.addEventListener('input', function() { renderModalOptions(this.value); });
    modalSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = this.value.trim();
            if (val && !state[activeKey].includes(val)) {
                state[activeKey].push(val);
                syncHidden(activeKey);
                renderTags(activeKey);
                this.value = '';
                renderModalOptions('');
            }
        }
        if (e.key === 'Escape') closeModal();
    });

    // Initial render
    renderTags('must_have_skills');
    renderTags('nice_to_have_skills');

    // Form submission - convert to arrays
    document.getElementById('vacancyForm').addEventListener('submit', function(e) {
        const mustHave = document.getElementById('must_have_skills').value;
        const niceToHave = document.getElementById('nice_to_have_skills').value;

        const createArrayInputs = (name, value) => {
            const items = value.split(',').map(s => s.trim()).filter(s => s);
            items.forEach(item => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name + '[]';
                input.value = item;
                this.appendChild(input);
            });
        };

        if (mustHave) createArrayInputs('must_have_skills', mustHave);
        if (niceToHave) createArrayInputs('nice_to_have_skills', niceToHave);
    });
});
</script>
@endpush
