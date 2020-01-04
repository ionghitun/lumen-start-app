<h1>@lang('forgot.title', ['name' => $user->name])</h1>
<p>@lang('forgot.codeInfo')</p>
<br/>
<p style="font-weight: bold">{{ $user->forgot_code }}</p>
<p>@lang('forgot.signature')</p>
