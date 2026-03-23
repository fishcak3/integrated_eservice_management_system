<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentRequest->documentType->name ?? 'Document' }} - {{ $documentRequest->tracking_code }}</title>

    <link rel="icon" href="{{ $global_logo ? asset('storage/' . $global_logo) : asset('favicon.ico') }}" type="image/png">
    <style>
        @page { margin: 1in; }
        body {
            font-family: 'Times New Roman', Times, serif; 
            color: #000;
            line-height: 1.5;
            font-size: 12pt;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 20%;
            left: 15%;
            width: 80%;
            opacity: 0.10; 
            z-index: -1000;
            text-align: center;
        }
        .watermark img { width: 100%; height: auto; }

        /* Header Table Layout */
        .header-table { 
            width: 100%; 
            margin-bottom: 20px; 
            border-collapse: collapse; 
        }
        .header-table td { 
            vertical-align: middle; 
            text-align: center; 
        }
        .header-logo { 
            width: 90px; 
            height: auto; 
        }
        .header-text p { margin: 0; padding: 0; line-height: 1.2; }
        .header-text .rep { font-size: 11pt; }
        .header-text .brgy { font-size: 16pt; font-weight: bold; margin-top: 10px; color: #000; }
        .separator { border-bottom: 2px solid #000; border-top: 1px solid #000; height: 2px; margin: 15px 0; }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 30px 0;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        /* Content Body */
        .content { margin-bottom: 30px; text-align: justify; }
        .salutation { font-weight: bold; font-size: 14pt; margin-bottom: 20px; }
        .indent { text-indent: 40px; margin-bottom: 15px; }
        .highlight { font-weight: bold; text-transform: uppercase; font-size: 13pt; }

        /* Footer & Signatures */
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            border: none;
        }
        .footer-table td {
            vertical-align: bottom; 
        }
        .left-footer {
            width: 50%;
            font-size: 10pt;
        }
        .right-footer {
            width: 50%;
            text-align: center;
        }
        .thumbmark-box {
            width: 80px;
            height: 100px;
            border: 1px solid #000;
            text-align: center;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    @if($global_logo)
        <div class="watermark">
            <img src="{{ public_path('storage/' . $global_logo) }}" alt="Logo">
        </div>
    @endif

    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                @if($global_logo)
                    <img src="{{ public_path('storage/' . $global_logo) }}" class="header-logo" alt="Barangay Logo">
                @endif
            </td>
            
            <td style="width: 60%;" class="header-text">
                <p class="rep">Republic of the Philippines</p>
                <p class="rep">Province of {{ $global_province }}</p>
                <p class="rep">Municipality of {{ $global_municipality }}</p>
                <p class="rep" style="font-weight: bold; margin-top: 5px;">BARANGAY {{ strtoupper($global_brgy_name) }}</p>
            </td>
            
            <td style="width: 20%;"></td>
        </tr>
    </table>

    <div class="separator"></div>

    {{-- FIX 1: Added text-align: center here --}}
    <p class="header" style="font-weight: bold; font-size: 14pt; text-align: center;">OFFICE OF THE PUNONG BARANGAY</p>

    <div class="doc-title">
        {{ $documentRequest->documentType->name ?? 'BARANGAY CERTIFICATE' }}
    </div>

    <div class="content">
        <div class="salutation">TO WHOM IT MAY CONCERN:</div>

        @php
            $docType = strtolower($documentRequest->documentType->name ?? '');
            $name = $documentRequest->printed_name ?? $documentRequest->requestor_name;
            $purpose = $documentRequest->purpose;
        @endphp

        @switch(true)
            @case(str_contains($docType, 'clearance') && !str_contains($docType, 'business'))
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span>, of legal age, Filipino citizen, is a bonafide resident of Barangay {{ $global_brgy_name }}, {{ $global_municipality }}, {{ $global_province }}.</p>
                <p class="indent">This certifies further that the above-named person is known to me to be of good moral character and a law-abiding citizen in the community. As per records kept in this office, he/she has <strong>NO PENDING DEROGATORY RECORD</strong> nor any pending case filed against him/her to date.</p>
                @break

            @case(str_contains($docType, 'indigency'))
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span>, of legal age, is a permanent and bonafide resident of Barangay {{ $global_brgy_name }}, {{ $global_municipality }}, {{ $global_province }}.</p>
                <p class="indent">This certifies further that the said resident belongs to an indigent family whose income is not sufficient to meet their daily basic needs.</p>
                @break

            @case(str_contains($docType, 'residency'))
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span>, of legal age, is a bonafide and permanent resident of Barangay {{ $global_brgy_name }}, {{ $global_municipality }}, {{ $global_province }}.</p>
                <p class="indent">Based on the records of this office, he/she has been residing in this barangay and is known to be a peaceful and law-abiding citizen.</p>
                @break

            @case(str_contains($docType, 'business'))
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span> is granted this Barangay Business Clearance to operate a business/trade activity within the territorial jurisdiction of Barangay {{ $global_brgy_name }}.</p>
                <p class="indent">The applicant is hereby advised to strictly follow and observe existing Barangay Ordinances and other rules and regulations regarding business operations.</p>
                <p class="indent"><strong>Business Details/Purpose:</strong> {{ $purpose }}</p>
                @break

            @case(str_contains($docType, 'moral'))
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span> is a registered and bonafide resident of Barangay {{ $global_brgy_name }}, {{ $global_municipality }}, {{ $global_province }}.</p>
                <p class="indent">This certification is issued to attest that the above-named individual possesses <strong>Good Moral Character</strong>, is a law-abiding citizen, and has actively participated in community building. He/She has not been convicted of any crime involving moral turpitude.</p>
                @break

            @case(str_contains($docType, 'objection'))
                <p class="indent">This is to certify that the Barangay Council of {{ $global_brgy_name }} interposes <strong>NO OBJECTION</strong> to the request of <span class="highlight">{{ $name }}</span> for the purpose of: <em>{{ $purpose }}</em>.</p>
                <p class="indent">This clearance is issued upon the condition that the applicant will comply with all legal requirements and local ordinances related to the said purpose.</p>
                @break

            @default
                <p class="indent">This is to certify that <span class="highlight">{{ $name }}</span>, is a resident of Barangay {{ $global_brgy_name }}, {{ $global_municipality }}.</p>
                <p class="indent">This certification is issued upon the request of the interested party for whatever legal intents and purposes it may serve.</p>
        @endswitch

        @if(!str_contains($docType, 'business') && !str_contains($docType, 'objection'))
            <p class="indent">This certification is being issued upon the request of the above-named person for: <strong>{{ strtoupper($purpose) }}</strong>.</p>
        @endif

        <p class="indent">Issued this <strong>{{ now()->format('jS') }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay {{ $global_brgy_name }}, {{ $global_municipality }}, {{ $global_province }}, Philippines.</p>
        
        @if($documentRequest->remarks)
            <p class="indent" style="font-size: 10pt; font-style: italic;">Remarks: {{ $documentRequest->remarks }}</p>
        @endif
    </div>

    {{-- FIX 2: Extracted Control Numbers outside the signature table so the heights remain balanced --}}
    <div style="font-size: 10pt; margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Control No.:</strong> {{ $documentRequest->control_number ?? '______________' }}</p>
        <p style="margin: 0;"><strong>O.R. No.:</strong> ______________</p>
        <p style="margin: 0;"><strong>Validity:</strong> {{ str_replace('_', ' ', Str::title($documentRequest->validity_period ?? '______________')) }}</p>
    </div>

    {{-- Official Footer Elements --}}
    <table class="footer-table">
        <tr>
            {{-- Left Side: Thumbmark and Applicant Signature --}}
            <td class="left-footer">
                <div class="thumbmark-box">
                    <br><br><br>
                    Right<br>Thumbmark
                </div>
                <div style="border-bottom: 1px solid #000; width: 200px; margin-top: 30px;"></div>
                <p style="font-size: 8pt; margin-top: 5px; margin-bottom: 0;">(Applicant's Signature / Thumbmark)</p>
            </td>
            
            {{-- Right Side: Captain Signature --}}
            <td class="right-footer">
                @if($documentRequest->is_e_signed && isset($official) && $official->e_signature_path)
                    @php
                        $path = storage_path('app/public/' . $official->e_signature_path);
                        $base64 = '';
                        if(file_exists($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp

                    {{-- FIX 3: Fixed-height wrapper and adjusted image sizing to prevent layout breaking --}}
                    <div style="height: 60px;">
                        @if($base64)
                            <img src="{{ $base64 }}" 
                                 style="height: 80px; width: auto; margin-bottom: -20px;" 
                                 alt="">
                        @else
                            <span style="color:red; font-size: 8px;">Signature file missing at: {{ $path }}</span>
                        @endif
                    </div>
                @else
                    <div style="height: 60px;"></div> 
                @endif

                <div style="border-bottom: 1px solid #000; width: 220px; margin: 0 auto;"></div>
                <p style="margin-top: 5px; margin-bottom: 0;"><strong>HON. {{ $captainName }}</strong></p>
                <p style="margin: 0; font-size: 10pt;">Punong Barangay</p>
            </td>
        </tr>
    </table>

</body>
</html>