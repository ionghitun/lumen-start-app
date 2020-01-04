<h1>@lang('emailChange.title', ['name' => $user->name])</h1>
<p>@lang('emailChange.codeInfo')</p>
<br/>
<p style="font-weight: bold">{{ $user->activation_code }}</p>
<p>@lang('emailChange.signature')</p>
