<?php
$source = htmlspecialchars($_GET['source'] ?? 'Website', ENT_QUOTES, 'UTF-8');
$media = htmlspecialchars($_GET['media'] ?? '', ENT_QUOTES, 'UTF-8');
$campaign = htmlspecialchars($_GET['campaign'] ?? '', ENT_QUOTES, 'UTF-8');
$basePath = htmlspecialchars($_GET['basePath'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<style>
.reg-form-container { max-width: 100%; }
.reg-form-container input, .reg-form-container select { width: 100%; padding: 10px 12px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
.reg-form-container .btn-submit { width: 100%; padding: 12px; background-color: #e0181e; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
.reg-form-container .btn-submit:hover { background-color: #c01518; }
.reg-form-container .form-title { font-size: 18px; font-weight: 700; margin-bottom: 15px; text-align: center; }
.reg-form-container .phone-group { display: flex; gap: 8px; }
.reg-form-container .phone-group select { width: 80px; flex-shrink: 0; }
.reg-form-container .phone-group input { flex: 1; }
.reg-form-container .consent { font-size: 12px; color: #666; margin: 8px 0 15px; }
.reg-form-container .consent input { width: auto; margin-right: 5px; }
.reg-form-container .msg { text-align: center; padding: 8px; margin-bottom: 10px; border-radius: 4px; display: none; font-size: 14px; }
.reg-form-container .msg.error { background: #ffe0e0; color: #c00; display: block; }
.reg-form-container .msg.success { background: #e0ffe0; color: #060; display: block; }
</style>
<div class="reg-form-container">
    <p class="form-title">Register Now</p>
    <div id="regFormMsg" class="msg"></div>
    <form id="regForm" onsubmit="return submitRegForm(event)">
        <input type="text" name="name" placeholder="Full Name *" required minlength="2" maxlength="100">
        <input type="email" name="email" placeholder="Email Address *" required>
        <div class="phone-group">
            <select name="countryCode"><option value="+91" selected>+91</option></select>
            <input type="tel" name="mobile" placeholder="Mobile Number *" required pattern="[0-9]{10}" maxlength="10">
        </div>
        <select name="program" required>
            <option value="">Select Program *</option>
        </select>
        <div class="consent">
            <label><input type="checkbox" name="consent" required> I agree to receive information about programs and services.</label>
        </div>
        <input type="hidden" name="source" value="<?php echo $source; ?>">
        <input type="hidden" name="media" value="<?php echo $media; ?>">
        <input type="hidden" name="campaign" value="<?php echo $campaign; ?>">
        <button type="submit" class="btn-submit">Submit</button>
    </form>
</div>
<script>
(function(){
    var form = document.getElementById('regForm');
    var sel = form.querySelector('select[name="program"]');
    fetch('<?php echo $basePath; ?>api/get-programs.php')
        .then(function(r){return r.json()})
        .then(function(data){
            data.forEach(function(item){
                var o = document.createElement('option');
                o.value = item.Code;
                o.textContent = item.ShortName;
                sel.appendChild(o);
            });
        }).catch(function(){});
})();
function submitRegForm(e){
    e.preventDefault();
    var f = document.getElementById('regForm');
    var msg = document.getElementById('regFormMsg');
    msg.className = 'msg'; msg.style.display = 'none';
    var btn = f.querySelector('.btn-submit');
    btn.disabled = true; btn.textContent = 'Submitting...';
    var data = {
        StudentName: f.name.value,
        StudentEmail: f.email.value,
        StudentMobile: f.mobile.value,
        StudentProgram: f.program.value,
        StudentSource: f.source.value || 'Website',
        StudentCountryCode: f.countryCode.value,
        mx_Param1: f.media.value || '',
        mx_Param2: f.campaign.value || '',
        mx_Param3: ''
    };
    var basePath = '<?php echo $basePath; ?>';
    fetch(basePath + 'api/submit-lead.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(function(r){return r.json()}).then(function(res){
        if(res.Status === 'Success'){
            msg.className = 'msg success'; msg.textContent = 'Registration successful!'; msg.style.display = 'block';
            f.reset();
            setTimeout(function(){ window.location.href = basePath + 'thanks.html'; }, 1500);
        } else {
            msg.className = 'msg error'; msg.textContent = res.Message || 'Submission failed.'; msg.style.display = 'block';
        }
        btn.disabled = false; btn.textContent = 'Submit';
    }).catch(function(){
        msg.className = 'msg error'; msg.textContent = 'Network error. Please try again.'; msg.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Submit';
    });
    return false;
}
</script>
