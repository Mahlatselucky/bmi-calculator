<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BMI Calculator</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #0a0f0d; --surface: #111a16; --card: #162019; --border: #1f3328;
      --green: #2dff7f; --green-dim: #1a9448; --yellow: #f5e642;
      --orange: #ff8c42; --red: #ff4d4d; --blue: #4dc3ff;
      --text: #d4ede0; --muted: #5a7a68;
      --font-display: 'Syne', sans-serif; --font-mono: 'Space Mono', monospace;
    }
    body {
      background: var(--bg); color: var(--text); font-family: var(--font-mono);
      min-height: 100vh; display: flex; flex-direction: column;
      align-items: center; padding: 0 1rem 3rem; overflow-x: hidden;
    }
    body::before {
      content: ''; position: fixed; top: -20%; left: 50%; transform: translateX(-50%);
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(45,255,127,0.07) 0%, transparent 70%);
      pointer-events: none; z-index: 0;
    }

    /* NAV */
    nav {
      width: 100%; max-width: 540px; display: flex; align-items: center;
      justify-content: space-between; padding: 1.2rem 0; position: relative; z-index: 1;
    }
    .nav-logo { font-family: var(--font-display); font-size: 1.1rem; font-weight: 800; color: #fff; }
    .nav-logo span { color: var(--green); }
    .nav-right { display: flex; align-items: center; gap: 1rem; }
    .nav-user { font-size: 0.65rem; color: var(--muted); letter-spacing: 0.08em; }
    .nav-user strong { color: var(--green); }
    .nav-links { display: flex; gap: 0.5rem; }
    .nav-links a {
      font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
      color: var(--muted); text-decoration: none; padding: 5px 10px;
      border: 1px solid var(--border); border-radius: 3px; transition: all 0.2s;
    }
    .nav-links a:hover { color: var(--green); border-color: var(--green-dim); }
    .nav-links a.active { color: var(--green); border-color: var(--green-dim); }

    /* Main */
    .wrapper { position: relative; z-index: 1; width: 100%; max-width: 520px; }
    header { text-align: center; margin-bottom: 2rem; }
    .label-tag {
      display: inline-block; background: rgba(45,255,127,0.1);
      border: 1px solid var(--green-dim); color: var(--green);
      font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase;
      padding: 4px 14px; border-radius: 2px; margin-bottom: 0.8rem;
    }
    h1 { font-family: var(--font-display); font-size: clamp(2.2rem,6vw,3rem); font-weight: 800; line-height: 1; color: #fff; }
    h1 span { color: var(--green); }
    header p { margin-top: 0.5rem; font-size: 0.72rem; color: var(--muted); }

    /* Card */
    .card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 8px; padding: 2rem; position: relative; overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, var(--green-dim), transparent);
    }

    /* Toggle */
    .unit-toggle {
      display: flex; background: var(--surface); border: 1px solid var(--border);
      border-radius: 4px; overflow: hidden; margin-bottom: 1.8rem;
    }
    .unit-toggle button {
      flex: 1; padding: 0.6rem; background: none; border: none; color: var(--muted);
      font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.1em;
      text-transform: uppercase; cursor: pointer; transition: all 0.2s;
    }
    .unit-toggle button.active { background: rgba(45,255,127,0.12); color: var(--green); }

    /* Fields */
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .field { display: flex; flex-direction: column; gap: 0.4rem; }
    .field.full { grid-column: 1 / -1; }
    label { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--muted); }
    .input-wrap {
      display: flex; align-items: center; background: var(--surface);
      border: 1px solid var(--border); border-radius: 4px; overflow: hidden; transition: border-color 0.2s;
    }
    .input-wrap:focus-within { border-color: var(--green-dim); }
    .input-wrap.error { border-color: var(--red); }
    input[type="number"] {
      flex: 1; background: none; border: none; outline: none; color: var(--text);
      font-family: var(--font-mono); font-size: 1rem; padding: 0.65rem 0.8rem; width: 100%;
    }
    input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; }
    .unit-badge {
      padding: 0 0.75rem; font-size: 0.65rem; color: var(--muted);
      border-left: 1px solid var(--border); white-space: nowrap;
    }
    .field-error { font-size: 0.6rem; color: var(--red); margin-top: 2px; min-height: 0.9rem; }

    /* Button */
    .btn-calc {
      width: 100%; margin-top: 1.2rem; padding: 0.9rem;
      background: var(--green); color: #000; border: none; border-radius: 4px;
      font-family: var(--font-display); font-size: 1rem; font-weight: 800;
      cursor: pointer; transition: all 0.15s;
    }
    .btn-calc:hover { background: #50ffaa; transform: translateY(-1px); }
    .btn-calc:active { transform: translateY(0); }

    /* Result */
    .result-panel { margin-top: 1.6rem; display: none; animation: fadeSlide 0.4s ease forwards; }
    .result-panel.visible { display: block; }
    @keyframes fadeSlide { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .divider { height: 1px; background: var(--border); margin: 1.5rem 0; }
    .result-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem; }
    .bmi-value { font-family: var(--font-display); font-size: 3.5rem; font-weight: 800; line-height: 1; transition: color 0.4s; }
    .bmi-category {
      font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase;
      padding: 4px 10px; border-radius: 2px; font-weight: 700; transition: all 0.4s;
    }
    .scale-track {
      height: 6px; border-radius: 3px;
      background: linear-gradient(90deg, #4dc3ff 0%, #2dff7f 28%, #f5e642 50%, #ff8c42 75%, #ff4d4d 100%);
      position: relative; margin-bottom: 0.5rem;
    }
    .scale-needle {
      position: absolute; top: 50%; transform: translate(-50%, -50%);
      width: 14px; height: 14px; background: #fff; border-radius: 50%;
      box-shadow: 0 0 0 3px rgba(0,0,0,0.5);
      transition: left 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .scale-labels { display: flex; justify-content: space-between; font-size: 0.55rem; color: var(--muted); margin-bottom: 1.2rem; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .info-cell { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; padding: 0.75rem; }
    .info-cell .cell-label { font-size: 0.6rem; color: var(--muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.3rem; }
    .info-cell .cell-value { font-family: var(--font-display); font-size: 1.05rem; font-weight: 600; }
    .tip-box {
      margin-top: 1rem; background: rgba(45,255,127,0.05); border: 1px solid rgba(45,255,127,0.15);
      border-radius: 4px; padding: 0.75rem 1rem; font-size: 0.7rem; color: var(--muted);
      line-height: 1.6; display: none;
    }
    .tip-box.visible { display: block; }
    .tip-box strong { color: var(--green); }

    /* Save confirmation */
    .save-confirm {
      margin-top: 0.75rem; font-size: 0.68rem; color: var(--green);
      text-align: center; display: none; animation: fadeSlide 0.3s ease;
    }
    .save-confirm.visible { display: block; }

    footer { margin-top: 2.5rem; text-align: center; font-size: 0.6rem; color: var(--muted); letter-spacing: 0.1em; z-index: 1; }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">BMI <span>Calc</span></div>
  <div class="nav-right">
    <span class="nav-user">Hello, <strong><?= $username ?></strong></span>
    <div class="nav-links">
      <a href="index.php" class="active">Calculator</a>
      <a href="history.php">History</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="wrapper">
  <header>
    <div class="label-tag">Health Metric Tool</div>
    <h1>BMI <span>Calc</span></h1>
    <p>Body Mass Index · Instant Analysis · Results Saved to Your Account</p>
  </header>

  <div class="card">
    <div class="unit-toggle">
      <button class="active" onclick="setUnit('metric')" id="btn-metric">Metric (kg / cm)</button>
      <button onclick="setUnit('imperial')" id="btn-imperial">Imperial (lbs / in)</button>
    </div>

    <div class="field-grid">
      <div class="field full">
        <label for="age">Age</label>
        <div class="input-wrap" id="wrap-age">
          <input type="number" id="age" placeholder="25" min="2" max="120" oninput="clearErr('age')"/>
          <span class="unit-badge">years</span>
        </div>
        <div class="field-error" id="err-age"></div>
      </div>
      <div class="field">
        <label for="weight">Weight</label>
        <div class="input-wrap" id="wrap-weight">
          <input type="number" id="weight" placeholder="70" min="1" oninput="clearErr('weight')"/>
          <span class="unit-badge" id="unit-weight">kg</span>
        </div>
        <div class="field-error" id="err-weight"></div>
      </div>
      <div class="field">
        <label for="height">Height</label>
        <div class="input-wrap" id="wrap-height">
          <input type="number" id="height" placeholder="170" min="1" oninput="clearErr('height')"/>
          <span class="unit-badge" id="unit-height">cm</span>
        </div>
        <div class="field-error" id="err-height"></div>
      </div>
    </div>

    <button class="btn-calc" onclick="calculate()">Calculate BMI →</button>

    <div class="result-panel" id="result-panel">
      <div class="divider"></div>
      <div class="result-header">
        <div>
          <div style="font-size:0.6rem;color:var(--muted);letter-spacing:0.15em;text-transform:uppercase;margin-bottom:4px;">Your BMI</div>
          <div class="bmi-value" id="bmi-value">—</div>
        </div>
        <div class="bmi-category" id="bmi-category">—</div>
      </div>
      <div class="scale-track">
        <div class="scale-needle" id="scale-needle" style="left:0%"></div>
      </div>
      <div class="scale-labels">
        <span>Underweight</span><span>Normal</span><span>Overweight</span><span>Obese</span>
      </div>
      <div class="info-grid">
        <div class="info-cell">
          <div class="cell-label">Healthy Weight Range</div>
          <div class="cell-value" id="healthy-range">—</div>
        </div>
        <div class="info-cell">
          <div class="cell-label">Weight Status</div>
          <div class="cell-value" id="weight-diff">—</div>
        </div>
        <div class="info-cell">
          <div class="cell-label">BMI Prime</div>
          <div class="cell-value" id="bmi-prime">—</div>
        </div>
        <div class="info-cell">
          <div class="cell-label">Ponderal Index</div>
          <div class="cell-value" id="ponderal">—</div>
        </div>
      </div>
      <div class="tip-box" id="tip-box"></div>
      <div class="save-confirm" id="save-confirm">✓ Result saved to your history</div>
    </div>
  </div>
</div>

<footer>BMI is a screening tool, not a diagnostic measure. Consult a healthcare provider.</footer>

<script>
  let currentUnit = 'metric';

  function setUnit(unit) {
    currentUnit = unit;
    document.getElementById('btn-metric').classList.toggle('active', unit === 'metric');
    document.getElementById('btn-imperial').classList.toggle('active', unit === 'imperial');
    document.getElementById('unit-weight').textContent = unit === 'metric' ? 'kg' : 'lbs';
    document.getElementById('unit-height').textContent = unit === 'metric' ? 'cm' : 'in';
    document.getElementById('weight').placeholder = unit === 'metric' ? '70' : '154';
    document.getElementById('height').placeholder = unit === 'metric' ? '170' : '67';
    document.getElementById('result-panel').classList.remove('visible');
  }

  function clearErr(field) {
    document.getElementById('err-' + field).textContent = '';
    document.getElementById('wrap-' + field).classList.remove('error');
  }

  function setErr(field, msg) {
    document.getElementById('err-' + field).textContent = msg;
    document.getElementById('wrap-' + field).classList.add('error');
  }

  function validate(age, weight, height) {
    let valid = true;
    if (!age || age < 2 || age > 120) { setErr('age', 'Enter a valid age (2–120).'); valid = false; }
    if (!weight || weight <= 0)        { setErr('weight', 'Enter a valid weight.'); valid = false; }
    if (!height || height <= 0)        { setErr('height', 'Enter a valid height.'); valid = false; }
    if (currentUnit === 'metric' && height > 300)   { setErr('height', 'Height seems too large (cm).'); valid = false; }
    if (currentUnit === 'imperial' && height > 120) { setErr('height', 'Height seems too large (in).'); valid = false; }
    return valid;
  }

  function calculate() {
    const age    = parseFloat(document.getElementById('age').value);
    const weight = parseFloat(document.getElementById('weight').value);
    const height = parseFloat(document.getElementById('height').value);

    if (!validate(age, weight, height)) return;

    const weightKg = currentUnit === 'metric' ? weight : weight * 0.453592;
    const heightM  = currentUnit === 'metric' ? height / 100 : height * 0.0254;
    const bmi      = weightKg / (heightM * heightM);
    const bmiPrime = bmi / 25;
    const ponderal = weightKg / Math.pow(heightM, 3);

    const { category, color, bgColor, tip } = classify(bmi);
    const minKg = 18.5 * heightM * heightM;
    const maxKg = 24.9 * heightM * heightM;

    let rangeStr, diffStr;
    if (currentUnit === 'metric') {
      rangeStr = `${minKg.toFixed(1)}–${maxKg.toFixed(1)} kg`;
      const diff = weightKg - (bmi < 18.5 ? minKg : bmi > 24.9 ? maxKg : weightKg);
      diffStr = Math.abs(diff) < 0.5 ? 'Ideal ✓' : (diff < 0 ? `${Math.abs(diff).toFixed(1)} kg under` : `${diff.toFixed(1)} kg over`);
    } else {
      rangeStr = `${(minKg*2.20462).toFixed(1)}–${(maxKg*2.20462).toFixed(1)} lbs`;
      const diff = weightKg - (bmi < 18.5 ? minKg : bmi > 24.9 ? maxKg : weightKg);
      const diffLbs = diff * 2.20462;
      diffStr = Math.abs(diffLbs) < 1 ? 'Ideal ✓' : (diffLbs < 0 ? `${Math.abs(diffLbs).toFixed(1)} lbs under` : `${diffLbs.toFixed(1)} lbs over`);
    }

    document.getElementById('bmi-value').textContent = bmi.toFixed(1);
    document.getElementById('bmi-value').style.color = color;
    const catEl = document.getElementById('bmi-category');
    catEl.textContent = category; catEl.style.background = bgColor; catEl.style.color = color;
    document.getElementById('healthy-range').textContent = rangeStr;
    document.getElementById('weight-diff').textContent = diffStr;
    document.getElementById('bmi-prime').textContent = bmiPrime.toFixed(2);
    document.getElementById('ponderal').textContent = `${ponderal.toFixed(1)} kg/m³`;

    const pct = Math.min(Math.max(((bmi - 15) / (40 - 15)) * 100, 2), 98);
    document.getElementById('scale-needle').style.left = pct + '%';

    const tipEl = document.getElementById('tip-box');
    tipEl.innerHTML = `<strong>💡 Note:</strong> ${tip}`;
    tipEl.classList.add('visible');

    document.getElementById('result-panel').classList.add('visible');
    document.getElementById('save-confirm').classList.remove('visible');

    // Save to PHP backend
    sendToBackend({ age, weight, height, unit: currentUnit, bmi: bmi.toFixed(2), category });
  }

  function classify(bmi) {
    if (bmi < 18.5) return { category:'Underweight', color:'#4dc3ff', bgColor:'rgba(77,195,255,0.12)', tip:'BMI below 18.5 may indicate insufficient nutrition. Consider consulting a dietitian.' };
    if (bmi < 25)   return { category:'Normal',      color:'#2dff7f', bgColor:'rgba(45,255,127,0.12)', tip:'Your BMI is in the healthy range. Maintain a balanced diet and regular activity.' };
    if (bmi < 30)   return { category:'Overweight',  color:'#f5e642', bgColor:'rgba(245,230,66,0.12)', tip:'BMI 25–29.9 is considered overweight. Moderate exercise and diet changes can help.' };
    if (bmi < 35)   return { category:'Obese I',     color:'#ff8c42', bgColor:'rgba(255,140,66,0.12)', tip:'Class I obesity. Lifestyle modifications and medical guidance are recommended.' };
    if (bmi < 40)   return { category:'Obese II',    color:'#ff6b35', bgColor:'rgba(255,107,53,0.12)', tip:'Class II obesity. Please consult a healthcare professional for a personalized plan.' };
    return           { category:'Obese III',   color:'#ff4d4d', bgColor:'rgba(255,77,77,0.12)',  tip:'Class III (severe) obesity. Medical intervention is strongly advised.' };
  }

  async function sendToBackend(data) {
    try {
      const res  = await fetch('bmi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json();
      if (json.status === 'success') {
        document.getElementById('save-confirm').classList.add('visible');
      }
    } catch(e) { console.log('Backend not reachable.'); }
  }
</script>
</body>
</html>
