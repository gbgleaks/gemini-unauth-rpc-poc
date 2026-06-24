<?php
/**
 * Project: Kaptch - High-Speed Captcha Solver
 * Website: kaptch.com (c) 2026
 * Lead Developer: Adnan Awan (gbgleaks@gmail.com)
 */

if (isset($_POST['ajax_request'])) {
    error_reporting(0);
    $base64 = "";

    // Image file processing
    if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] != "") {
        $image_data = file_get_contents($_FILES['file']['tmp_name']);
        $base64 = urlencode(base64_encode($image_data));
    } elseif (isset($_POST['url']) && $_POST['url'] != "") {
        $image_data = @file_get_contents($_POST['url']);
        if($image_data) {
            $base64 = urlencode(base64_encode($image_data));
        }
    }

    if ($base64 != "") {
        // Constructing raw batchexecute payload injection
        $data = 'f.req=%5B%5B%5B%22q4uTj%22%2C%22%5Bnull%2C%5C%22%7B%5C%5C%5C%22contents%5C%5C%5C%22%3A%5B%7B%5C%5C%5C%22role%5C%5C%5C%22%3A%5C%5C%5C%22user%5C%5C%5C%22%2C%5C%5C%5C%22parts%5C%5C%5C%22%3A%5B%7B%5C%5C%5C%22text%5C%5C%5C%22%3A%5C%5C%5C%22Please%20read%20the%20text%20inside%20this%20CAPTCHA%20image%20and%20return%20ONLY%20the%20characters%20in%20a%20single%20line.%20Ignore%20background%20noise%20and%20focus%20only%20on%20the%20main%20text%2C%20including%20any%20superscript%20or%20irregular%20characters.%20If%20the%20result%20is%20\'Yxn8\'%2C%20return%20\'Yxn8\'.%5C%5C%5C%22%7D%2C%7B%5C%5C%5C%22inlineData%5C%5C%5C%22%3A%7B%5C%5C%5C%22mimeType%5C%5C%5C%22%3A%5C%5C%5C%22image%2Fjpeg%5C%5C%5C%22%2C%5C%5C%5C%22data%5C%5C%5C%22%3A%5C%5C%5C%22'.$base64.'%5C%5C%5C%22%7D%7D%5D%7D%5D%7D%5C%22%2C1%5D%22%2Cnull%2C%22generic%22%5D%5D%5D&at=AGElXSNm5BzhSWSXFUoa082A8aKs%3A1761211604316&';
        $api_url = "https://gemini.google.com/_/BardChatUi/data/batchexecute?rpcids=q4uTj&source-path=%2Fapp%2F5ae079d64d69531b&bl=boq_assistant-bard-web-server_20251021.09_p0&f.sid=8417126538979636234&hl=en&_reqid=58705207&rt=c";

        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($curl);
        curl_close($curl);

        // Parsing JSON structural layers out of raw response chunks
        $replace_result = str_replace('\\\\\\"text\\\\\\":\\\\\\"',"&text=",$resp);
        $replace_result = str_replace('\\\\\\"',"&",$replace_result);
        $replace_result = str_replace('\\\\n', '', $replace_result);
        parse_str($replace_result, $out_result);
        
        if (isset($out_result['text'])) {
            $decoded_text = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }, $out_result['text']);
            $final_text = stripslashes(stripslashes($decoded_text));
            
            echo json_encode(["status" => "success", "result" => trim($final_text)]);
        } else {
            echo json_encode(["status" => "error", "message" => "Decoding failed. Internal mapping signature mismatch."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Please select or provide a valid image target."]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaptch | Direct AI Captcha Solver & Decoder</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root { --main-blue: #3b82f6; --bg-navy: #0f172a; --card-navy: #1e293b; }
        body { background-color: var(--bg-navy) !important; color: #ffffff !important; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .navbar { background: #1e293b !important; border-bottom: 1px solid #334155; padding: 15px 0; }
        .card-custom { background: var(--card-navy) !important; border: 1px solid #334155 !important; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
        .fix-white { color: #ffffff !important; font-weight: 600; }
        .form-control { background: #0f172a !important; border: 1px solid #475569 !important; color: #ffffff !important; padding: 12px; }
        .upload-area { border: 2px dashed #475569; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; background: rgba(15, 23, 42, 0.5); }
        #fileInput { display: none; }
        .btn-kaptch { background: var(--main-blue) !important; color: white !important; font-weight: 700; padding: 14px; border-radius: 12px; width: 100%; border: none; transition: 0.3s; }
        .btn-kaptch:hover { transform: translateY(-2px); }
        .btn-kaptch:disabled { background: #475569 !important; opacity: 0.6; cursor: not-allowed; transform: none; }
        #resultBox { display: none; background: #064e3b !important; color: #6ee7b7 !important; padding: 20px; border-radius: 15px; margin-top: 20px; border: 1px solid #065f46; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">KAPTCH<span class="text-primary">.</span></a>
        <span class="navbar-text ms-auto text-secondary d-none d-sm-inline">Standalone Infrastructure PoC</span>
    </div>
</nav>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <span class="badge bg-primary px-3 py-2 mb-3 shadow-sm">Zero Configuration Required</span>
            <h1 class="fw-bold display-4 mb-3">Direct AI <br><span class="text-primary">Captcha Solver</span></h1>
            <p class="lead text-light mb-4">A functional standalone deployment demonstrating unauthenticated Layer-7 model execution using production routing channels.</p>
        </div>

        <div class="col-lg-5 offset-lg-1">
            <div class="card card-custom p-4">
                <h2 class="h5 fix-white mb-4 text-center">Try Decoder Ready Tool</h2>
                <form id="mainSolverForm" enctype="multipart/form-data">
                    <input type="hidden" name="ajax_request" value="1">
                    <div class="mb-3">
                        <label class="small fix-white mb-2 d-block">Upload Captcha Image</label>
                        <div class="upload-area" id="dropZone" onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-cloud-upload-alt text-primary fs-2 mb-2"></i>
                            <p class="small mb-0 text-secondary" id="fileName">Click to upload or drag image</p>
                            <input type="file" name="file" id="fileInput" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small fix-white mb-2 d-block">Or Image URL</label>
                        <input type="text" name="url" class="form-control" placeholder="Paste image link here...">
                    </div>
                    <button type="submit" id="btnSubmit" class="btn btn-kaptch shadow">DECODE CAPTCHA</button>
                </form>

                <div id="resultBox" class="text-center shadow">
                    <span class="d-block small text-uppercase fw-bold text-white-50 mb-1">Decoded Text Output:</span>
                    <div id="decodedText" class="display-6 fw-bold text-white mb-0"></div>
                </div>
                <div id="errorBox" class="text-center pt-3" style="display:none;">
                    <div id="errorMsg" class="text-danger fw-bold"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="py-4 bg-dark text-center border-top border-secondary mt-5">
    <p class="text-secondary small mb-0">© 2026 Developed by Adnan Awan (gbgleaks). Karachi, Pakistan.</p>
</footer>

<script>
$(document).ready(function() {
    $('#fileInput').change(function() {
        var file = this.files[0];
        if (file) { $('#fileName').text(file.name).addClass('text-white'); }
    });

    $('#mainSolverForm').on('submit', function(e) {
        e.preventDefault();
        $('#resultBox, #errorBox').hide();
        
        // Disable button and change state text
        var submitBtn = $('#btnSubmit');
        submitBtn.prop('disabled', true).text('DECODING, PLEASE WAIT...');
        
        var formData = new FormData(this);
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if(res.status === "success") {
                    $('#decodedText').text(res.result);
                    $('#resultBox').fadeIn();
                } else {
                    $('#errorMsg').text(res.message);
                    $('#errorBox').fadeIn();
                }
            },
            error: function() {
                $('#errorMsg').text("Routing error or parsing format failed.");
                $('#errorBox').fadeIn();
            },
            complete: function() {
                // Re-enable button and reset text once request completes
                submitBtn.prop('disabled', false).text('DECODE CAPTCHA');
            }
        });
    });
});
</script>
</body>
</html>