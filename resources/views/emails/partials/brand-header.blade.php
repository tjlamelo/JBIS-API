{{-- Header commun MyJob Best --}}
<tr>
    <td style="background:#0f172a; color:#ffffff; padding:20px 24px; text-align:center;">
        <img
            src="{{ $logoUrl }}"
            alt="{{ $brandName }} — {{ $companyName }}"
            width="120"
            height="120"
            style="display:block; margin:0 auto 12px; width:120px; height:120px; object-fit:contain; border:0;"
        >
        <p style="margin:0; font-size:18px; font-weight:700; letter-spacing:0.02em;">{{ $brandName }}</p>
        @isset($headerSubtitle)
            <p style="margin:8px 0 0; font-size:13px; opacity:0.9;">{{ $headerSubtitle }}</p>
        @endisset
    </td>
</tr>
