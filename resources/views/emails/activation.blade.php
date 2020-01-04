<h1>@lang('activate.title', ['name' => $user->name])</h1>
<p>@lang('activate.codeInfo')</p>
<br/>
<p style="font-weight: bold">{{ $user->activation_code }}</p>
<p>@lang('activate.signature')</p>
