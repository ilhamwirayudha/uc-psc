{{-- BIOGRAPHY FORM (ENGLISH) --}}
<div class="space-y-6">

    {{-- CARD: CONSENT STATEMENT --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Declaration of Authenticity <span class="text-red-500">*</span></h3>
        </div>

        <div>
            <p class="text-sm text-[#6B5B85] leading-relaxed mb-3">
                All the information I have provided in this Biography Form is true and made in a good faith.
            </p>

            <label class="inline-flex items-center gap-2.5 cursor-pointer text-sm text-[#2A2035] font-semibold">
                <input type="checkbox" name="consent_agree" value="Agree" required checked class="w-4 h-4 rounded text-purple-deep focus:ring-purple-deep border-[#D9C2F0]">
                <span>Agree <span class="text-red-500">*</span></span>
            </label>
            @error('consent_agree') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- CARD 1: PERSONAL INFORMATION --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Personal Information</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Your full name..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Gender <span class="text-red-500">*</span></label>
                <select name="gender" required class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                    <option value="">-- Select Gender --</option>
                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Place and Date of Birth <span class="text-red-500">*</span></label>
                <input type="text" name="birth_place_date" value="{{ old('birth_place_date') }}" required placeholder="e.g. Surabaya, August 12, 2004" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Order of Birth (Optional)</label>
                <input type="text" name="birth_order" value="{{ old('birth_order') }}" placeholder="e.g. 1st child of 3 siblings" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">City of Origin (Optional)</label>
                <input type="text" name="city_of_origin" value="{{ old('city_of_origin') }}" placeholder="e.g. Surabaya" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Address <span class="text-red-500">*</span></label>
                <textarea name="current_address" rows="2" required placeholder="Current residential address..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('current_address') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxx" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Active Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Religion/Belief <span class="text-red-500">*</span></label>
                <input type="text" name="religion" value="{{ old('religion') }}" required placeholder="e.g. Christian / Catholic / Muslim / Buddhist / Hindu / Other" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Ethnicity (Optional)</label>
                <input type="text" name="ethnicity" value="{{ old('ethnicity') }}" placeholder="e.g. Javanese, Chinese, etc" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>

            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Highest Level of Education <span class="text-red-500">*</span></label>
                <input type="text" name="last_education" value="{{ old('last_education') }}" required placeholder="e.g. High School Grade 12 / Bachelor's Degree" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>
    </div>

    {{-- CARD 2: FAMILY INFORMATION --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-6">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Family Information</h3>
                    <p class="text-xs text-slate-500">Information about parents and siblings (optional).</p>
                </div>
            </div>
        </div>

        {{-- Father's Information --}}
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-bold text-purple-deep border-b border-[#EDE1FA] pb-1.5">Father's Information</h4>
            <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Father's Full Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="Father's name" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Father's Gender</label>
                    <select name="father_gender" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                        <option value="Male" {{ old('father_gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('father_gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Father's Age</label>
                    <input type="text" name="father_age" value="{{ old('father_age') }}" placeholder="e.g. 56 Years" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Highest Level of Father's Education</label>
                    <input type="text" name="father_education" value="{{ old('father_education') }}" placeholder="e.g. Bachelor's" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Father's Occupation</label>
                    <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" placeholder="Father's job" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- Mother's Information --}}
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-bold text-purple-deep border-b border-[#EDE1FA] pb-1.5">Mother's Information</h4>
            <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Mother's Full Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="Mother's name" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Mother's Gender</label>
                    <select name="mother_gender" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                        <option value="Female" {{ old('mother_gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Male" {{ old('mother_gender') === 'Male' ? 'selected' : '' }}>Male</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Mother's Age</label>
                    <input type="text" name="mother_age" value="{{ old('mother_age') }}" placeholder="e.g. 52 Years" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Highest Level of Mother's Education</label>
                    <input type="text" name="mother_education" value="{{ old('mother_education') }}" placeholder="e.g. High School" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Mother's Occupation</label>
                    <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" placeholder="Mother's job" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>

        {{-- Sibling Information (1 to 5) --}}
        <div class="space-y-3">
            <h4 class="text-sm font-bold text-purple-deep">Sibling Information (1 to 5)</h4>
            @for($s = 1; $s <= 5; $s++)
            <div class="p-3.5 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#5B4A73]">Sibling {{ $s }} <span class="text-slate-400 font-normal">(Optional)</span></span>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-2.5">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-[#5B4A73] mb-0.5">Sibling {{ $s }}'s Name</label>
                        <input type="text" name="sibling_{{ $s }}_name" value="{{ old('sibling_' . $s . '_name') }}" placeholder="Name" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#5B4A73] mb-0.5">Gender</label>
                        <select name="sibling_{{ $s }}_gender" class="w-full px-2 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                            <option value="">--</option>
                            <option value="Male" {{ old('sibling_' . $s . '_gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sibling_' . $s . '_gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#5B4A73] mb-0.5">Age</label>
                        <input type="text" name="sibling_{{ $s }}_age" value="{{ old('sibling_' . $s . '_age') }}" placeholder="Age" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#5B4A73] mb-0.5">Education</label>
                        <input type="text" name="sibling_{{ $s }}_education" value="{{ old('sibling_' . $s . '_education') }}" placeholder="Education" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-bold text-[#5B4A73] mb-0.5">Occupation</label>
                        <input type="text" name="sibling_{{ $s }}_occupation" value="{{ old('sibling_' . $s . '_occupation') }}" placeholder="Occupation" class="w-full px-2.5 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-xs">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- CARD 3: FORMAL EDUCATION HISTORY --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div>
                    <h3 class="text-base font-bold text-purple-deep">Formal Education History</h3>
                    <p class="text-xs text-slate-500">List from the most recent (optional).</p>
                </div>
            </div>
        </div>

        {{-- School 1 --}}
        <div class="p-4 bg-purple-50/20 rounded-xl border border-purple-200/80 space-y-3">
            <h4 class="text-sm font-bold text-purple-deep border-b border-purple-100 pb-1.5">School 1 (Most Recent)</h4>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Name of School 1</label>
                    <input type="text" name="school_1_name" value="{{ old('school_1_name') }}" placeholder="School / University name" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">City of School 1</label>
                    <input type="text" name="school_1_city" value="{{ old('school_1_city') }}" placeholder="City" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Additional Information 1 (Major/Program)</label>
                    <input type="text" name="school_1_info" value="{{ old('school_1_info') }}" placeholder="e.g. Science / Business" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Year of Entry 1</label>
                    <input type="text" name="school_1_year_entry" value="{{ old('school_1_year_entry') }}" placeholder="Year of entry" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Year of Graduation 1</label>
                    <input type="text" name="school_1_year_graduation" value="{{ old('school_1_year_graduation') }}" placeholder="Year of graduation" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                </div>
            </div>
        </div>

        {{-- Schools 2 to 5 --}}
        @for($sk = 2; $sk <= 5; $sk++)
        <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
            <h4 class="text-sm font-bold text-purple-deep border-b border-[#EDE1FA] pb-1.5">School {{ $sk }} <span class="text-slate-400 font-normal">(Optional)</span></h4>
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Name of School {{ $sk }}</label>
                    <input type="text" name="school_{{ $sk }}_name" value="{{ old('school_' . $sk . '_name') }}" placeholder="School name" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">City of School {{ $sk }}</label>
                    <input type="text" name="school_{{ $sk }}_city" value="{{ old('school_' . $sk . '_city') }}" placeholder="City" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Additional Info {{ $sk }}</label>
                    <input type="text" name="school_{{ $sk }}_info" value="{{ old('school_' . $sk . '_info') }}" placeholder="Major/Program" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Year of Entry {{ $sk }}</label>
                    <input type="text" name="school_{{ $sk }}_year_entry" value="{{ old('school_' . $sk . '_year_entry') }}" placeholder="Year of entry" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#5B4A73] mb-1">Year of Graduation {{ $sk }}</label>
                    <input type="text" name="school_{{ $sk }}_year_graduation" value="{{ old('school_' . $sk . '_year_graduation') }}" placeholder="Year of graduation" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                </div>
            </div>
        </div>
        @endfor

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5 leading-snug">
                Have you ever failed a grade? If yes, which grade and what was the reason? (Optional)
            </label>
            <textarea name="failed_grade_reason" rows="2" placeholder="e.g. Never / In grade ... because ..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('failed_grade_reason') }}</textarea>
        </div>
    </div>

    {{-- CARD 4: NON-FORMAL EDUCATION --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <div>
                <h3 class="text-base font-bold text-purple-deep">Non-Formal Education</h3>
                <p class="text-xs text-slate-500">List from the most recent (optional).</p>
            </div>
        </div>

        <div class="space-y-4">
            @for($c = 1; $c <= 3; $c++)
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-bold text-purple-deep">Course / Training {{ $c }}</h4>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Course Type {{ $c }}</label>
                        <input type="text" name="course_{{ $c }}_type" value="{{ old('course_' . $c . '_type') }}" placeholder="e.g. English Course" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Place / City {{ $c }}</label>
                        <input type="text" name="course_{{ $c }}_place" value="{{ old('course_' . $c . '_place') }}" placeholder="Institution / City" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Duration {{ $c }}</label>
                        <input type="text" name="course_{{ $c }}_duration" value="{{ old('course_' . $c . '_duration') }}" placeholder="e.g. 6 Months" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- CARD 5: ORGANIZATIONAL EXPERIENCE --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <div>
                <h3 class="text-base font-bold text-purple-deep">Organizational Experience</h3>
                <p class="text-xs text-slate-500">List from the most recent (optional).</p>
            </div>
        </div>

        <div class="space-y-4">
            @for($o = 1; $o <= 3; $o++)
            <div class="p-4 bg-[#FAF9FD] rounded-xl border border-[#EDE1FA]/80 space-y-3">
                <h4 class="text-sm font-bold text-purple-deep">Organization {{ $o }}</h4>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Organization Name {{ $o }}</label>
                        <input type="text" name="org_{{ $o }}_name" value="{{ old('org_' . $o . '_name') }}" placeholder="Organization name" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Position {{ $o }}</label>
                        <input type="text" name="org_{{ $o }}_position" value="{{ old('org_' . $o . '_position') }}" placeholder="Position / Role" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5B4A73] mb-1">Duration {{ $o }}</label>
                        <input type="text" name="org_{{ $o }}_duration" value="{{ old('org_' . $o . '_duration') }}" placeholder="e.g. 1 Year" class="w-full px-3 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm">
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- CARD 6: ACHIEVEMENTS --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Achievements</h3>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5 leading-snug">
                If you have ever achieved any achievements, please write them down (academic or non-academic).
            </label>
            <textarea name="achievements" rows="3" placeholder="List your achievements (optional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('achievements') }}</textarea>
        </div>
    </div>

    {{-- CARD 7: SELF DESCRIPTION & CHARACTERISTICS --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-5">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Self Description & Characteristics</h3>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Traumatic experiences in life (if any).</label>
            <textarea name="traumatic_experience" rows="2" placeholder="Your answer (optional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('traumatic_experience') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">History of illness requiring hospitalization (if any).</label>
            <textarea name="hospitalization_history" rows="2" placeholder="Your answer (optional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('hospitalization_history') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Have you ever consulted a psychologist before? If yes, state type and purpose.</label>
            <textarea name="psychologist_consultation" rows="2" placeholder="Your answer (optional)..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('psychologist_consultation') }}</textarea>
        </div>

        {{-- Personal Strengths 1, 2, 3 --}}
        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Strength 1 (Optional)</label>
                <input type="text" name="personal_strength_1" value="{{ old('personal_strength_1') }}" placeholder="Strength 1" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Strength 2 (Optional)</label>
                <input type="text" name="personal_strength_2" value="{{ old('personal_strength_2') }}" placeholder="Strength 2" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Strength 3 (Optional)</label>
                <input type="text" name="personal_strength_3" value="{{ old('personal_strength_3') }}" placeholder="Strength 3" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Weakness 1 (Optional)</label>
                <input type="text" name="personal_weakness_1" value="{{ old('personal_weakness_1') }}" placeholder="Weakness 1" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Weakness 2 (Optional)</label>
                <input type="text" name="personal_weakness_2" value="{{ old('personal_weakness_2') }}" placeholder="Weakness 2" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Personal Weakness 3 (Optional)</label>
                <input type="text" name="personal_weakness_3" value="{{ old('personal_weakness_3') }}" placeholder="Weakness 3" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>
    </div>

    {{-- CARD 8: OTHER INFORMATION --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
        <div class="pb-3 border-b border-[#EDE1FA] flex items-center gap-2">
            <h3 class="text-base font-bold text-purple-deep">Other Information</h3>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Subject with Highest Score (Optional)</label>
                <input type="text" name="subject_highest_score" value="{{ old('subject_highest_score') }}" placeholder="Subject name..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Subject with Lowest Score (Optional)</label>
                <input type="text" name="subject_lowest_score" value="{{ old('subject_lowest_score') }}" placeholder="Subject name..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Subject that You Like (Optional)</label>
                <input type="text" name="subject_liked" value="{{ old('subject_liked') }}" placeholder="Subject you like..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Subject That You Dislike (Optional)</label>
                <input type="text" name="subject_disliked" value="{{ old('subject_disliked') }}" placeholder="Subject you dislike..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Learning Style (Optional)</label>
                <input type="text" name="learning_style" value="{{ old('learning_style') }}" placeholder="Visual / Auditory / Kinesthetic / etc" class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Things/Activities that You Like (Optional)</label>
                <input type="text" name="activities_liked" value="{{ old('activities_liked') }}" placeholder="Activities you enjoy..." class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Things/Activities that You Dislike (Optional)</label>
                <input type="text" name="activities_disliked" value="{{ old('activities_disliked') }}" placeholder="Activities you dislike..." class="w-full px-3.5 py-2 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Dream Job (Optional)</label>
            <input type="text" name="dream_job" value="{{ old('dream_job') }}" placeholder="Your dream career or job..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">What You Have Done to Achieve Your Dream Job (Optional)</label>
                <textarea name="efforts_for_dream_job" rows="2" placeholder="Efforts made so far..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('efforts_for_dream_job') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">What Will You Do to Achieve Your Dream Job (Optional)</label>
                <textarea name="plans_for_dream_job" rows="2" placeholder="Future plans..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('plans_for_dream_job') }}</textarea>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Field of Work that You Like (Optional)</label>
                <input type="text" name="work_field_liked" value="{{ old('work_field_liked') }}" placeholder="Preferred career fields..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Field of Work that You Dislike (Optional)</label>
                <input type="text" name="work_field_disliked" value="{{ old('work_field_disliked') }}" placeholder="Disliked fields..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Activities that You Like but aren't Skilled at (Optional)</label>
                <input type="text" name="activities_liked_not_skilled" value="{{ old('activities_liked_not_skilled') }}" placeholder="e.g. Painting, coding..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
            <div>
                <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Activities that You Dislike but Quite Skilled at (Optional)</label>
                <input type="text" name="activities_disliked_quite_skilled" value="{{ old('activities_disliked_quite_skilled') }}" placeholder="e.g. Administrative work..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Describe yourself freely in 1-2 paragraphs. (Optional)</label>
            <textarea name="free_self_description" rows="3" placeholder="Describe yourself freely in 1-2 paragraphs..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('free_self_description') }}</textarea>
        </div>
    </div>

</div>
