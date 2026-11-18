{{-- BIOGRAPHY FORM (ENGLISH) — Diselaraskan 1-to-1 dengan Formulir Publik /daftar/biography-en --}}
<div class="space-y-6">

    {{-- CARD: CONSENT STATEMENT --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Declaration of Authenticity <span class="text-rose-300 font-bold">*</span></h3>
        </div>

        <div class="p-6 space-y-4">
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                All the information I have provided in this Biography Form is true and made in good faith.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm font-medium text-black">
                <input type="checkbox" name="consent_agree" value="Agree" required {{ old('consent_agree') === 'Agree' ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Agree <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- CARD 1: PERSONAL INFORMATION --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Personal Information</h3>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Your complete legal name..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Gender <span class="text-red-500">*</span></label>
                <select name="gender" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Select Gender --</option>
                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Place and Date of Birth <span class="text-red-500">*</span></label>
                <input type="text" name="birth_place_date" value="{{ old('birth_place_date') }}" required placeholder="e.g. Surabaya, August 12, 2004" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Birth Order in Family</label>
                <input type="text" name="birth_order" value="{{ old('birth_order') }}" placeholder="e.g. 1st child of 3 siblings" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Current Residential Address <span class="text-red-500">*</span></label>
                <textarea name="current_address" rows="2" required placeholder="Current residential address..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('current_address') }}</textarea>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Mobile / WhatsApp Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="numeric" pattern="[0-9]+" title="Numbers only (0-9)" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onkeydown="if (!/[0-9]/.test(event.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End', 'Enter'].includes(event.key) && !event.ctrlKey && !event.metaKey) event.preventDefault()" placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Active Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Religion / Faith <span class="text-red-500">*</span></label>
                    <select name="religion" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Select Religion / Faith --</option>
                        @foreach(['Islam', 'Protestant Christian', 'Catholic', 'Hinduism', 'Buddhism', 'Confucianism', 'Other'] as $rel)
                            <option value="{{ $rel }}" {{ old('religion') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Ethnicity</label>
                    <input type="text" name="ethnicity" value="{{ old('ethnicity') }}" placeholder="e.g. Javanese, Chinese, etc" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Highest Level of Education <span class="text-red-500">*</span></label>
                    <select name="last_education" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Select Education --</option>
                        @foreach(['Kindergarten (TK)', 'Elementary School (SD)', 'Junior High School (SMP)', 'Senior High School / Vocational (SMA/SMK)', 'Diploma (D3/D4)', "Bachelor's Degree (S1)", "Master's Degree (S2)", 'Doctorate (S3)', 'Other'] as $edu)
                            <option value="{{ $edu }}" {{ old('last_education') === $edu ? 'selected' : '' }}>{{ $edu }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Current Occupation</label>
                    <input type="text" name="occupation" value="{{ old('occupation') }}" placeholder="e.g. Student, Graphic Designer" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: FAMILY BACKGROUND --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Family Background</h3>
                <p class="text-xs text-white mt-0.5">Information about parents and siblings (optional).</p>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Father's Profile --}}
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Father's Profile</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Father's Name</label>
                        <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="Father's full name" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Age</label>
                        <input type="text" name="father_age" value="{{ old('father_age') }}" placeholder="e.g. 55" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Highest Education</label>
                        <input type="text" name="father_education" value="{{ old('father_education') }}" placeholder="e.g. Bachelor's" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Occupation</label>
                        <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" placeholder="e.g. Entrepreneur" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>

            {{-- Mother's Profile --}}
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-semibold text-black border-b border-[#EDE1FA] pb-1.5">Mother's Profile</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Mother's Name</label>
                        <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="Mother's full name" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Age</label>
                        <input type="text" name="mother_age" value="{{ old('mother_age') }}" placeholder="e.g. 52" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Highest Education</label>
                        <input type="text" name="mother_education" value="{{ old('mother_education') }}" placeholder="e.g. Bachelor's" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Occupation</label>
                        <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" placeholder="e.g. Homemaker" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Siblings Information</label>
                <textarea name="siblings_info" rows="3" placeholder="List name, age, and occupation of your brothers / sisters..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('siblings_info') }}</textarea>
            </div>
        </div>
    </div>

    {{-- CARD 3: EDUCATION & EXPERIENCE --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Education &amp; Experience</h3>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Education History</label>
                <textarea name="school_history" rows="2" placeholder="Schools / Universities attended with majors..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('school_history') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Work / Internship Experience</label>
                <textarea name="work_history" rows="2" placeholder="Companies, positions, and responsibilities..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('work_history') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Organizational / Volunteer Experience</label>
                <textarea name="organizational_experience" rows="2" placeholder="Clubs, committees, or volunteer roles..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('organizational_experience') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Hobbies &amp; Interests</label>
                    <input type="text" name="hobbies_interests" value="{{ old('hobbies_interests') }}" placeholder="e.g. Reading, Swimming, Music" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Dream Job / Career Goal</label>
                    <input type="text" name="dream_job" value="{{ old('dream_job') }}" placeholder="Your dream career or job..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 4: PERSONAL PROFILE & LIFE HISTORY --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Personal Profile &amp; Life History</h3>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Reason for Seeking Counseling</label>
                <textarea name="counseling_reason" rows="3" placeholder="What concerns or struggles bring you to counseling?" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('counseling_reason') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Current Psychological / Emotional Condition</label>
                <textarea name="current_condition" rows="2" placeholder="Describe how you are feeling lately..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('current_condition') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Strengths &amp; Areas for Improvement</label>
                <textarea name="strengths_weaknesses" rows="2" placeholder="Your main strengths and things you would like to develop..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('strengths_weaknesses') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Past Traumatic Events (Optional)</label>
                <textarea name="trauma_history" rows="2" placeholder="Significant past experiences that still affect you..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('trauma_history') }}</textarea>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Medical / Physical Health Conditions (Optional)</label>
                <textarea name="medical_history" rows="2" placeholder="Chronic illnesses, medications, or health concerns..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('medical_history') }}</textarea>
            </div>
        </div>
    </div>

    {{-- CARD 5: PREFERENCES & EMERGENCY CONTACT --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        <div class="bg-purple-deep px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Preferences &amp; Emergency Contact</h3>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Previous Counseling?</label>
                    <select name="previous_counseling" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                        <option value="">-- Select --</option>
                        <option value="Yes" {{ old('previous_counseling') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('previous_counseling') === 'No' ? 'selected' : '' }}>Never</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Previous Counselor (Optional)</label>
                    <input type="text" name="previous_counselor" value="{{ old('previous_counselor') }}" placeholder="Counselor / psychologist name" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Emergency Contact</label>
                <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="Name, Relationship, and Phone Number" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-[13px] font-medium text-slate-500 mb-1.5">Preferred Counseling Format <span class="text-red-500">*</span></label>
                <select name="preferred_counseling" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Select Format --</option>
                    <option value="Offline at UC PSC" {{ old('preferred_counseling') === 'Offline at UC PSC' ? 'selected' : '' }}>Offline at UC PSC</option>
                    <option value="Online via Zoom" {{ old('preferred_counseling') === 'Online via Zoom' ? 'selected' : '' }}>Online via Zoom</option>
                </select>
            </div>
        </div>
    </div>

</div>
