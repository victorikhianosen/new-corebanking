<p style="margin:0 0 16px 0; font-size:16px;">Dear {{ $tenant->name }},</p>

<p>We're pleased to let you know that your organization <strong>{{ $tenant->name }}</strong>
has been successfully set up on our core banking platform.</p>

<p>Your workspace is now active and ready to use. Below are your access details:</p>

<table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px 0; width:100%;">
    <tr>
        <td style="padding:6px 0; color:#64748b;">Tenant Code</td>
        <td style="padding:6px 0; font-weight:600; color:#0f172a;">{{ $tenant->code }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0; color:#64748b;">Organization</td>
        <td style="padding:6px 0; font-weight:600; color:#0f172a;">{{ $tenant->name }}</td>
    </tr>
    <tr>
        <td style="padding:6px 0; color:#64748b;">Status</td>
        <td style="padding:6px 0; font-weight:600; color:#059669;">Active</td>
    </tr>
</table>

<p>Please keep your tenant code safe — you'll need it to sign in.</p>

{{-- CTA button lives here now --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0;">
    <tr>
        <td align="center" bgcolor="#059669" style="border-radius:8px;">
            <a href="https://yourbank.app/login" target="_blank"
               style="display:inline-block; padding:13px 30px; font-family:'Segoe UI', Arial, sans-serif; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:8px;">
                Log in to your dashboard
            </a>
        </td>
    </tr>
</table>