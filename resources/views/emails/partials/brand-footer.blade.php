{{-- Footer commun MyJob Best --}}
<tr>
    <td style="background:#f8fafc; border-top:1px solid #e5e7eb; padding:16px 24px; text-align:center; color:#6b7280; font-size:12px;">
        @isset($footerNote)
            <p style="margin:0 0 8px;">{{ $footerNote }}</p>
        @endisset
        <p style="margin:0;">
            &copy; {{ date('Y') }} {{ $brandName }}
            <span style="opacity:0.75;">· {{ $companyName }}</span>
        </p>
    </td>
</tr>
