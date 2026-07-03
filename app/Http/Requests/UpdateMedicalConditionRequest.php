<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalConditionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // يمكنك إضافة تحقق صلاحيات هنا لاحقاً
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'condition'    => 'sometimes|required|string|max:255',
            'treatment'    => 'nullable|string|max:500',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'notes'        => 'nullable|string',
        ];
    }

    /**
     * رسائل الخطأ بالعربي (اختياري لكن موصى به)
     */
    public function messages()
    {
        return [
            'condition.required'    => 'اسم الحالة الطبية مطلوب',
            'condition.string'      => 'اسم الحالة يجب أن يكون نصاً',
            'start_date.date'       => 'تاريخ البداية يجب أن يكون تاريخ صحيح',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية أو يساويه',
        ];
    }
}
