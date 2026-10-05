<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Biographical Intake Form — UC PSC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html { 
            font-size: 100% !important;
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Montserrat', sans-serif; 
            background-color: #FAF9FD;
            font-size: 16px;
        }
        [x-cloak] { display: none !important; }

        /* Typography Sizing for Online Form */
        label.block.font-bold,
        label.font-bold {
            font-size: 0.9375rem !important;
            line-height: 1.4 !important;
        }
        @media (min-width: 640px) {
            label.block.font-bold,
            label.font-bold {
                font-size: 1rem !important;
            }
        }

        p.text-xs.text-slate-500,
        span.text-xs.text-slate-500,
        .text-xs.text-slate-500,
        p.text-slate-500 {
            font-size: 0.8125rem !important;
            line-height: 1.45 !important;
        }

        input[type="text"],
        input[type="date"],
        input[type="tel"],
        input[type="email"],
        input[type="number"],
        textarea,
        select {
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
        }

        label.cursor-pointer,
        label.cursor-pointer span,
        label.cursor-pointer div {
            font-size: 0.875rem !important;
        }

        button[type="submit"],
        button[type="button"] {
            font-size: 0.875rem !important;
        }

        p.text-rose-600 {
            font-size: 0.775rem !important;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-purple-deep selection:text-white pb-16 relative">

    {{-- Ambient UC PSC Brand Background Atmosphere --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-[#F3EAFB] via-[#FAF9FD]/80 to-transparent rounded-full blur-3xl opacity-90"></div>
        <div class="absolute top-48 -right-24 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl"></div>
        <div class="absolute top-96 -left-20 w-80 h-80 bg-blue-100/20 rounded-full blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <main class="flex-1 py-6 sm:py-8 px-4" x-data="{
        page: 1,
        totalPages: 6,
        errors: {},

        // Safety Draft State
        draftSaved: false,
        hasRestoredDraft: false,
        formSubmitted: false,
        draftTimer: null,
        lastSavedTime: '',

        // Form Fields
        consent_agree: '{{ old('consent_agree', '') }}',
        
        // Page 2: Personal Data
        full_name: '{{ old('full_name', '') }}',
        gender: '{{ old('gender', '') }}',
        birth_place_date: '{{ old('birth_place_date', '') }}',
        birth_order: '{{ old('birth_order', '') }}',
        current_address: '{{ old('current_address', '') }}',
        phone: '{{ old('phone', '') }}',
        email: '{{ old('email', '') }}',
        religion: '{{ old('religion', '') }}',
        ethnicity: '{{ old('ethnicity', '') }}',
        last_education: '{{ old('last_education', '') }}',
        occupation: '{{ old('occupation', '') }}',

        // Page 3: Family Background
        father_name: '{{ old('father_name', '') }}',
        father_age: '{{ old('father_age', '') }}',
        father_education: '{{ old('father_education', '') }}',
        father_occupation: '{{ old('father_occupation', '') }}',
        mother_name: '{{ old('mother_name', '') }}',
        mother_age: '{{ old('mother_age', '') }}',
        mother_education: '{{ old('mother_education', '') }}',
        mother_occupation: '{{ old('mother_occupation', '') }}',
        siblings_info: '{{ old('siblings_info', '') }}',

        // Page 4: Education & Experience
        school_history: '{{ old('school_history', '') }}',
        work_history: '{{ old('work_history', '') }}',
        organizational_experience: '{{ old('organizational_experience', '') }}',
        achievements: '{{ old('achievements', '') }}',
        hobbies_interests: '{{ old('hobbies_interests', '') }}',
        dream_job: '{{ old('dream_job', '') }}',

        // Page 5: Profile & Life History
        counseling_reason: '{{ old('counseling_reason', '') }}',
        current_condition: '{{ old('current_condition', '') }}',
        strengths_weaknesses: '{{ old('strengths_weaknesses', '') }}',
        life_goals: '{{ old('life_goals', '') }}',
        fears_phobias: '{{ old('fears_phobias', '') }}',
        trauma_history: '{{ old('trauma_history', '') }}',
        medical_history: '{{ old('medical_history', '') }}',

        // Page 6: Preferences & Emergency
        previous_counseling: '{{ old('previous_counseling', '') }}',
        previous_counselor: '{{ old('previous_counselor', '') }}',
        emergency_contact: '{{ old('emergency_contact', '') }}',
        info_source: '{{ old('info_source', '') }}',
        preferred_counseling: '{{ old('preferred_counseling', '') }}',

        init() {
            @if(!session('error') && !$errors->any())
                this.restoreDraft();
            @endif
        },

        saveDraft() {
            if (this.formSubmitted) return;
            const formEl = document.getElementById('bioEnForm');
            if (!formEl) return;

            const draft = {};
            const formData = new FormData(formEl);
            for (const [key, value] of formData.entries()) {
                if (key === '_token') continue;
                draft[key] = value;
            }

            const fieldKeys = [
                'consent_agree', 'full_name', 'gender', 'birth_place_date', 'birth_order',
                'current_address', 'phone', 'email', 'religion', 'ethnicity', 'last_education',
                'occupation', 'father_name', 'father_age', 'father_education', 'father_occupation',
                'mother_name', 'mother_age', 'mother_education', 'mother_occupation', 'siblings_info',
                'school_history', 'work_history', 'organizational_experience', 'achievements',
                'hobbies_interests', 'dream_job', 'counseling_reason', 'current_condition',
                'strengths_weaknesses', 'life_goals', 'fears_phobias', 'trauma_history',
                'medical_history', 'previous_counseling', 'previous_counselor', 'emergency_contact',
                'info_source', 'preferred_counseling'
            ];

            fieldKeys.forEach(k => {
                if (this[k] !== undefined && this[k] !== null && this[k] !== '') {
                    draft[k] = this[k];
                }
            });

            draft['_saved_page'] = this.page;
            draft['_saved_at'] = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            try {
                localStorage.setItem('ucpsc_draft_bio_en', JSON.stringify(draft));
                this.draftSaved = true;
                this.hasRestoredDraft = true;
                this.lastSavedTime = draft['_saved_at'];
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => {
                    this.draftSaved = false;
                }, 3000);
            } catch (err) {
                console.warn('Failed to save draft:', err);
            }
        },

        restoreDraft() {
            try {
                const raw = localStorage.getItem('ucpsc_draft_bio_en');
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                const keys = Object.keys(draft).filter(k => !k.startsWith('_'));
                if (keys.length === 0) return;

                keys.forEach(key => {
                    if (this.hasOwnProperty(key)) {
                        if (draft[key] !== undefined && draft[key] !== null) {
                            this[key] = draft[key];
                        }
                    }
                });

                this.$nextTick(() => {
                    const formEl = document.getElementById('bioEnForm');
                    if (formEl) {
                        keys.forEach(key => {
                            const field = formEl.elements[key];
                            if (field && !(field instanceof RadioNodeList) && field.type !== 'radio') {
                                field.value = draft[key];
                            }
                        });
                    }
                });

                if (draft._saved_page && draft._saved_page >= 1 && draft._saved_page <= this.totalPages) {
                    this.page = draft._saved_page;
                }

                this.hasRestoredDraft = true;
                this.lastSavedTime = draft._saved_at || '';
            } catch (err) {
                console.warn('Failed to restore draft:', err);
            }
        },

        clearDraftOnSubmit() {
            this.formSubmitted = true;
            try {
                localStorage.removeItem('ucpsc_draft_bio_en');
            } catch (e) {}
        },

        validateCurrentPage() {
            this.errors = {};

            if (this.page === 1) {
                if (!this.consent_agree) {
                    this.errors['consent_agree'] = 'You must agree to the Informed Consent to continue.';
                } else if (this.consent_agree === 'Disagree') {
                    this.errors['consent_agree'] = 'You must agree to the Informed Consent to proceed with counseling services.';
                }
            } else if (this.page === 2) {
                if (!this.full_name.trim()) this.errors['full_name'] = 'This question is required';
                if (!this.gender) this.errors['gender'] = 'This question is required';
                if (!this.birth_place_date.trim()) this.errors['birth_place_date'] = 'This question is required';
                if (!this.birth_order.trim()) this.errors['birth_order'] = 'This question is required';
                if (!this.current_address.trim()) this.errors['current_address'] = 'This question is required';
                if (!this.phone.trim()) this.errors['phone'] = 'This question is required';
                if (!this.religion.trim()) this.errors['religion'] = 'This question is required';
                if (!this.ethnicity.trim()) this.errors['ethnicity'] = 'This question is required';
                if (!this.last_education.trim()) this.errors['last_education'] = 'This question is required';
            } else if (this.page === 3) {
                // Family details (helpful background)
            } else if (this.page === 4) {
                // Education & experience
            } else if (this.page === 5) {
                // Profile & life history
            } else if (this.page === 6) {
                // Preferences
            }

            if (Object.keys(this.errors).length > 0) {
                let firstKey = Object.keys(this.errors)[0];
                let el = document.getElementById('card_' + firstKey);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            return true;
        },

        nextPage() {
            if (this.validateCurrentPage()) {
                this.page++;
                this.saveDraft();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.saveDraft();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }">
        <div class="max-w-3xl mx-auto space-y-4">

            {{-- FORM START --}}
            <form action="{{ route('public.client-form.biography-en.store') }}" 
                  method="POST" 
                  id="bioEnForm" 
                  @input.debounce.400ms="saveDraft()" 
                  @change="saveDraft()"
                  @submit="if(!validateCurrentPage()){ $event.preventDefault(); } else { clearDraftOnSubmit(); }">
                @csrf

                {{-- ============================================================== --}}
                {{-- PAGE 1 OF 6: INFORMED CONSENT                                  --}}
                {{-- ============================================================== --}}
                <div x-show="page === 1" class="space-y-4">
                    {{-- Banner Header Card (Signature UC PSC Gradient) --}}
                    <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Biographical Intake Form</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl">
                                This Biographical Intake Form provides comprehensive background information about yourself. All data provided will remain strictly confidential and will only be utilized for psychological consultation and counseling purposes.
                            </p>
                            <div class="pt-3 border-t border-white/20 text-xs sm:text-sm text-rose-200 font-medium">
                                * Indicates required question
                            </div>
                        </div>
                    </div>

                    {{-- Informed Consent Agreement Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
                        <div class="pb-3 border-b border-slate-100">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900">INFORMED CONSENT</h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Counseling Service Agreement — Universitas Ciputra PSC</p>
                        </div>

                        <div class="text-sm sm:text-base text-slate-700 leading-relaxed space-y-4 max-h-[380px] overflow-y-auto pr-3 custom-scrollbar border border-slate-100 rounded-xl p-4 sm:p-5 bg-slate-50/50">
                            <div>
                                <h4 class="font-bold text-purple-deep text-base sm:text-lg">Psychological Consultation Services</h4>
                                <p class="mt-1">Psychological consultation is aimed at helping you overcome personal or social difficulties, better understand yourself, and reach your goals in personal, professional, and relational life. Results may vary depending on active mutual collaboration.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">1. Confidentiality Principle</h4>
                                <p class="mt-1">All information disclosed during sessions and in this form is strictly confidential and protected in accordance with the Indonesian Psychological Code of Ethics (HIMPSI). No information will be shared with outside parties without your written consent.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">2. Voluntary Engagement</h4>
                                <p class="mt-1">Your participation in this counseling service is completely voluntary. You have the right to ask questions or seek clarifications at any stage of the process.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">3. Honesty & Openness</h4>
                                <p class="mt-1">Providing honest and comprehensive answers helps our counselors gain an accurate understanding of your concerns and design the most effective interventions.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Consent Agreement Card --}}
                    <div id="card_consent_agree" class="bg-white rounded-2xl border shadow-xs p-6 space-y-3 transition-colors"
                         :class="errors['consent_agree'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-base sm:text-lg font-bold text-slate-900 leading-snug">
                            By agreeing to this form, you acknowledge that: <span class="text-rose-500">*</span>
                        </label>
                        <ul class="text-sm sm:text-base text-slate-600 leading-relaxed space-y-1.5 list-disc list-inside pl-1">
                            <li>You have read, understood, and agreed to the informed consent terms outlined above.</li>
                            <li>You agree to participate in psychological counseling services consciously and voluntarily.</li>
                        </ul>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-base"
                                   :class="consent_agree === 'Agree' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="consent_agree" value="Agree" x-model="consent_agree" @change="delete errors['consent_agree']"
                                       class="w-4 h-4 text-purple-deep focus:ring-purple-deep">
                                <span>I Agree</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-base"
                                   :class="consent_agree === 'Disagree' ? 'border-rose-400 bg-rose-50/70 text-rose-700' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="consent_agree" value="Disagree" x-model="consent_agree" @change="delete errors['consent_agree']"
                                       class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                                <span>I Disagree</span>
                            </label>
                        </div>

                        <template x-if="errors['consent_agree']">
                            <p class="text-xs sm:text-sm text-rose-600 font-medium pt-1 flex items-center gap-1.5" x-text="errors['consent_agree']"></p>
                        </template>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <div></div>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Next</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- PAGE 2 OF 6: PERSONAL DATA                                     --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">PERSONAL DATA</h2>
                        <p class="text-sm text-slate-500 mt-1">Please complete your identity details below.</p>
                    </div>

                    {{-- 1. Full Name --}}
                    <div id="card_full_name" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['full_name'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="full_name" x-model="full_name" @input="delete errors['full_name']"
                               placeholder="Your complete legal name" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['full_name']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['full_name']"></p>
                        </template>
                    </div>

                    {{-- 2. Gender --}}
                    <div id="card_gender" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['gender'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gender <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="gender === 'Male' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="gender" value="Male" x-model="gender" @change="delete errors['gender']" class="sr-only">
                                <span>Male</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="gender === 'Female' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="gender" value="Female" x-model="gender" @change="delete errors['gender']" class="sr-only">
                                <span>Female</span>
                            </label>
                        </div>
                        <template x-if="errors['gender']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['gender']"></p>
                        </template>
                    </div>

                    {{-- 3. Place & Date of Birth --}}
                    <div id="card_birth_place_date" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['birth_place_date'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Place & Date of Birth <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="birth_place_date" x-model="birth_place_date" @input="delete errors['birth_place_date']"
                               placeholder="e.g. Surabaya, 15 March 1998" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['birth_place_date']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['birth_place_date']"></p>
                        </template>
                    </div>

                    {{-- 4. Birth Order --}}
                    <div id="card_birth_order" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['birth_order'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Birth Order in Family <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="birth_order" x-model="birth_order" @input="delete errors['birth_order']"
                               placeholder="e.g. 1st of 3 siblings" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['birth_order']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['birth_order']"></p>
                        </template>
                    </div>

                    {{-- 5. Current Address --}}
                    <div id="card_current_address" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['current_address'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Current Residential Address <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="current_address" x-model="current_address" @input="delete errors['current_address']" rows="2"
                                  placeholder="Full current address with city" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['current_address']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['current_address']"></p>
                        </template>
                    </div>

                    {{-- 6. Contact Information --}}
                    <div id="card_phone" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['phone'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Contact Details <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">WhatsApp / Phone Number: <span class="text-rose-500">*</span></span>
                                <input type="tel" name="phone" x-model="phone" @input="delete errors['phone']"
                                       placeholder="e.g. +62 812 3456 7890" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['phone']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['phone']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Email Address:</span>
                                <input type="email" name="email" x-model="email"
                                       placeholder="name@email.com" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- 7. Religion & Ethnicity --}}
                    <div id="card_religion" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['religion'] || errors['ethnicity'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Religion & Ethnicity <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Religion / Faith:</span>
                                <input type="text" name="religion" x-model="religion" @input="delete errors['religion']"
                                       placeholder="e.g. Christian, Muslim, Catholic, etc" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['religion']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['religion']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Ethnicity:</span>
                                <input type="text" name="ethnicity" x-model="ethnicity" @input="delete errors['ethnicity']"
                                       placeholder="e.g. Javanese, Chinese, etc" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['ethnicity']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['ethnicity']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 8. Education & Occupation --}}
                    <div id="card_last_education" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['last_education'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Education & Occupation <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Highest Education Level:</span>
                                <input type="text" name="last_education" x-model="last_education" @input="delete errors['last_education']"
                                       placeholder="e.g. Bachelor's Degree in Psychology" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['last_education']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['last_education']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Current Occupation:</span>
                                <input type="text" name="occupation" x-model="occupation"
                                       placeholder="e.g. Student, Graphic Designer, etc" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Back
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Next</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- PAGE 3 OF 6: FAMILY BACKGROUND                                 --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">FAMILY BACKGROUND</h2>
                        <p class="text-sm text-slate-500 mt-1">Information regarding your parents and immediate family members.</p>
                    </div>

                    {{-- Father's Information --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Father's Profile</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Father's Name:</span>
                                <input type="text" name="father_name" x-model="father_name" placeholder="Father's full name" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Age:</span>
                                <input type="text" name="father_age" x-model="father_age" placeholder="e.g. 55" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Highest Education:</span>
                                <input type="text" name="father_education" x-model="father_education" placeholder="e.g. Bachelor's" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Occupation:</span>
                                <input type="text" name="father_occupation" x-model="father_occupation" placeholder="e.g. Entrepreneur" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Mother's Information --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Mother's Profile</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Mother's Name:</span>
                                <input type="text" name="mother_name" x-model="mother_name" placeholder="Mother's full name" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Age:</span>
                                <input type="text" name="mother_age" x-model="mother_age" placeholder="e.g. 52" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Highest Education:</span>
                                <input type="text" name="mother_education" x-model="mother_education" placeholder="e.g. Bachelor's" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Occupation:</span>
                                <input type="text" name="mother_occupation" x-model="mother_occupation" placeholder="e.g. Homemaker" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Siblings Information --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Siblings Information</label>
                        <p class="text-xs text-slate-500">List name, age, and occupation of your brothers / sisters.</p>
                        <textarea name="siblings_info" x-model="siblings_info" rows="3" placeholder="e.g. 1. Brother, 24, Software Engineer; 2. Sister, 19, University Student" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Back
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Next</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- PAGE 4 OF 6: EDUCATION & EXPERIENCE                            --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">EDUCATION & EXPERIENCE</h2>
                        <p class="text-sm text-slate-500 mt-1">Academic history, achievements, and career aspirations.</p>
                    </div>

                    {{-- Education History --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Education History</label>
                        <textarea name="school_history" x-model="school_history" rows="2" placeholder="Schools/Universities attended with majors..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Work & Organizational Experience --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Work / Internship Experience</label>
                            <textarea name="work_history" x-model="work_history" rows="2" placeholder="Companies, positions, and responsibilities..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Organizational / Volunteer Experience</label>
                            <textarea name="organizational_experience" x-model="organizational_experience" rows="2" placeholder="Clubs, committees, or volunteer roles..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                    </div>

                    {{-- Hobbies & Dream Job --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Hobbies & Interests:</span>
                                <input type="text" name="hobbies_interests" x-model="hobbies_interests" placeholder="e.g. Reading, Swimming, Music" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Dream Job / Career Goal:</span>
                                <input type="text" name="dream_job" x-model="dream_job" placeholder="e.g. Corporate Psychologist, Lead Designer" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Back
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Next</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- PAGE 5 OF 6: PERSONAL PROFILE & LIFE HISTORY                   --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">PERSONAL PROFILE & LIFE HISTORY</h2>
                        <p class="text-sm text-slate-500 mt-1">Understanding your main reasons for seeking counseling and your life experiences.</p>
                    </div>

                    {{-- Reason for Counseling --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Reason for Seeking Counseling</label>
                        <textarea name="counseling_reason" x-model="counseling_reason" rows="3" placeholder="What concerns or struggles bring you to counseling at this time?" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Current Condition & Life Goals --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Current Psychological / Emotional Condition</label>
                            <textarea name="current_condition" x-model="current_condition" rows="2" placeholder="Describe how you are feeling lately..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Strengths & Areas for Improvement</label>
                            <textarea name="strengths_weaknesses" x-model="strengths_weaknesses" rows="2" placeholder="Your main strengths and things you would like to develop..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                    </div>

                    {{-- Trauma & Medical History --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Trauma & Medical History (Optional)</label>
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Past Traumatic Events (if any):</span>
                                <textarea name="trauma_history" x-model="trauma_history" rows="2" placeholder="Significant past experiences that still affect you today..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Medical / Physical Health Conditions (if any):</span>
                                <textarea name="medical_history" x-model="medical_history" rows="2" placeholder="Chronic illnesses, current medications, or health concerns..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Back
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Next</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- PAGE 6 OF 6: PREFERENCES & EMERGENCY CONTACT                  --}}
                {{-- ============================================================== --}}
                <div x-show="page === 6" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">PREFERENCES & EMERGENCY CONTACT</h2>
                        <p class="text-sm text-slate-500 mt-1">Final step: session format preferences and emergency contact.</p>
                    </div>

                    {{-- Previous Counseling --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Have You Ever Had Counseling Before?</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="previous_counseling === 'Yes' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="previous_counseling" value="Yes" x-model="previous_counseling" class="sr-only">
                                <span>Yes, I Have</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="previous_counseling === 'No' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="previous_counseling" value="No" x-model="previous_counseling" class="sr-only">
                                <span>Never</span>
                            </label>
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Emergency Contact</label>
                        <input type="text" name="emergency_contact" x-model="emergency_contact" placeholder="Name, Relationship, and Phone Number (e.g. John Doe - Brother - 08123456789)" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- Preferred Counseling Format --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Preferred Counseling Format</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <label class="p-4 rounded-xl border-2 transition cursor-pointer flex items-start gap-3 hover:bg-slate-50"
                                   :class="preferred_counseling === 'Online via Zoom' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="preferred_counseling" value="Online via Zoom" x-model="preferred_counseling" class="mt-1 text-purple-deep focus:ring-purple-deep">
                                <div>
                                    <p class="font-bold text-sm">Online via Zoom</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Interactive virtual session via Zoom Meeting.</p>
                                </div>
                            </label>
                            <label class="p-4 rounded-xl border-2 transition cursor-pointer flex items-start gap-3 hover:bg-slate-50"
                                   :class="preferred_counseling === 'Offline at UC PSC' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="preferred_counseling" value="Offline at UC PSC" x-model="preferred_counseling" class="mt-1 text-purple-deep focus:ring-purple-deep">
                                <div>
                                    <p class="font-bold text-sm">Offline at UC PSC</p>
                                    <p class="text-xs text-slate-500 mt-0.5">In-person session at UC PSC Consultation Rooms, Universitas Ciputra Surabaya Campus.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar (Final Submit) --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Back
                        </button>
                        <button type="submit" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Submit Registration Form</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Footer Branding --}}
            <footer class="pt-6 text-center text-xs text-slate-500 space-y-1">
                <p>Official Registration Form of Universitas Ciputra Psychological Service Center (UC PSC).</p>
                <p>&copy; {{ date('Y') }} Universitas Ciputra Surabaya. All Rights Reserved.</p>
            </footer>

        </div>
    </main>

</body>
</html>
