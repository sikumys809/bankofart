/*
 * collect-simulator.js
 * ABOUT ページ「コレクトシミュレーター」（タブ式：即時償却 / 減価償却）。
 * mockups/about__17_.html の <script>（2059-2301 行）から計算ロジックを
 * 1文字も改変せず verbatim 移植。素の JS（jQuery不使用）。
 * IIFE でスコープ化するため page-about.js とは変数衝突しない。
 * フッターで読み込む（DOM 要素の後）＝モックのスクリプト位置と同等。
 */
(function(){
  'use strict';

  function fmt(n){ return Math.round(n).toLocaleString('ja-JP'); }
  function fmtMan(n){ return Math.round(n / 10000).toLocaleString('ja-JP') + '万'; }

  function incomeTax(x){
    if (x <= 0) return 0;
    if (x < 1950000)  return x * 0.05;
    if (x < 3300000)  return x * 0.10 -  97500;
    if (x < 6950000)  return x * 0.20 - 427500;
    if (x < 9000000)  return x * 0.23 - 636000;
    if (x < 18000000) return x * 0.33 - 1536000;
    if (x < 40000000) return x * 0.40 - 2796000;
    return x * 0.45 - 4796000;
  }

  // タブ切替
  document.querySelectorAll('.sim-tab').forEach(function(tab){
    tab.addEventListener('click', function(){
      var target = this.dataset.tab;
      document.querySelectorAll('.sim-tab').forEach(function(t){ t.classList.toggle('is-active', t.dataset.tab === target); });
      document.querySelectorAll('.sim-panel').forEach(function(p){ p.classList.toggle('is-active', p.dataset.panel === target); });
    });
  });

  // ════════ 即時償却シミュレーター ════════
  var UNIT = 390000;
  var CAP = 3000000;
  var DEP = 0.25;

  var csBiz = document.getElementById('cs-bizTaxToggle');
  csBiz.addEventListener('click', function(){
    csBiz.classList.toggle('is-on');
    document.getElementById('cs-sole-biztype').style.display = csBiz.classList.contains('is-on') ? '' : 'none';
  });

  document.getElementById('cs-entity').addEventListener('change', function(){
    var isCorp = this.value === 'corp';
    document.getElementById('cs-corp-region').style.display = isCorp ? '' : 'none';
    document.getElementById('cs-sole-region').style.display = isCorp ? 'none' : '';
    document.getElementById('cs-sole-income').style.display = isCorp ? 'none' : '';
    document.getElementById('cs-sole-tax-toggle').style.display = isCorp ? 'none' : '';
    document.getElementById('cs-sole-biztype').style.display = (isCorp || !csBiz.classList.contains('is-on')) ? 'none' : '';
  });

  document.getElementById('cs-resale').addEventListener('input', function(){
    document.getElementById('cs-resaleVal').textContent = this.value + '%';
  });
  document.getElementById('cs-qty').addEventListener('input', function(){
    document.getElementById('cs-qtyVal').textContent = this.value + '点';
  });

  document.getElementById('cs-calc').addEventListener('click', function(){
    var entity = document.getElementById('cs-entity').value;
    var qty = parseInt(document.getElementById('cs-qty').value, 10);
    var resaleRate = parseInt(document.getElementById('cs-resale').value, 10) / 100;
    var investment = UNIT * qty;

    var immediateBase = Math.min(investment, CAP);
    var excess = Math.max(0, investment - CAP);
    var excessFirstYear = excess * DEP;
    var deductible = immediateBase + excessFirstYear;

    var taxSaving = 0;
    var warnings = [];

    if (entity === 'corp') {
      var corpRate = parseFloat(document.getElementById('cs-corpRate').value);
      taxSaving = deductible * corpRate;
    } else {
      var income = parseInt(document.getElementById('cs-income').value, 10);
      var soleRate = parseFloat(document.getElementById('cs-soleRate').value);
      var taxableAfter = Math.max(0, income - deductible);
      var incomeTaxSaving = incomeTax(income) - incomeTax(taxableAfter);
      var fukko = incomeTaxSaving * 0.021;
      var resTaxSaving = Math.min(deductible, income) * soleRate;
      taxSaving = incomeTaxSaving + fukko + resTaxSaving;

      if (csBiz.classList.contains('is-on')) {
        var bizRate = parseFloat(document.getElementById('cs-bizRate').value);
        var bizBaseBefore = Math.max(0, income - 2900000);
        var bizBaseAfter = Math.max(0, income - deductible - 2900000);
        var bizSaving = (bizBaseBefore - bizBaseAfter) * bizRate;
        taxSaving += bizSaving;
      }

      if (deductible > income) {
        warnings.push('経費額が課税所得を上回っています。課税所得を超える分は当年の節税には使えません（純損失の繰越等は本試算の対象外です）。');
      }
    }

    var resaleRevenue = investment * resaleRate;
    var netResult = taxSaving + resaleRevenue - investment;

    if (investment > CAP) {
      warnings.push('投資額が年間300万円を超えています。超過分は当年の即時償却の対象外となり、法定耐用年数8年（定率法）の減価償却に移行します。本試算では超過分の初年度償却額のみを反映しています。');
    }

    var netLabel = netResult >= 0 ? '実質メリット' : '実質コスト';
    var netSign = netResult >= 0 ? '+' : '−';
    var netClass = netResult >= 0 ? '' : 'is-negative';
    var netAbs = Math.abs(netResult);

    var warnHtml = warnings.map(function(w){ return '<div class="sim-warning">' + w + '</div>'; }).join('');

    document.getElementById('cs-result').innerHTML =
      '<div class="sim-result-h">試算結果</div>' +
      '<div class="sim-result-row"><span class="sim-result-label">投資額</span><span class="sim-result-value">' + fmtMan(investment) + '円</span></div>' +
      '<div class="sim-result-row"><span class="sim-result-label">MERIT 01　税金圧縮</span><span class="sim-result-value">' + fmtMan(taxSaving) + '円</span></div>' +
      '<div class="sim-result-row"><span class="sim-result-label">MERIT 02　売却収益</span><span class="sim-result-value">' + fmtMan(resaleRevenue) + '円</span></div>' +
      '<div class="sim-result-row is-final"><span class="sim-result-label">' + netLabel + '</span><span class="sim-result-value ' + netClass + '">' + netSign + fmtMan(netAbs) + '円</span></div>' +
      warnHtml +
      '<div class="sim-notice">即時償却の対象は、資本金1億円以下・青色申告・常時使用従業員数400人以下の中小企業者等です。</div>';
  });

  // ════════ 減価償却シミュレーター ════════
  document.getElementById('ds-entity').addEventListener('change', function(){
    var isCorp = this.value === 'corp';
    document.getElementById('ds-corp-region').style.display = isCorp ? '' : 'none';
    document.getElementById('ds-sole-region').style.display = isCorp ? 'none' : '';
    document.getElementById('ds-sole-income').style.display = isCorp ? 'none' : '';
  });
  document.getElementById('ds-qty').addEventListener('input', function(){
    document.getElementById('ds-qtyVal').textContent = this.value + '点';
  });

  function depreciationSchedule(investment){
    var rate = 0.25, recoveryRate = 0.334, years = 8;
    var guarantee = investment * 0.07909;
    var book = investment;
    var schedule = [];
    var switched = false, recoveryBase = 0;
    for (var y = 1; y <= years; y++) {
      var straightDep = book * rate;
      var dep;
      if (!switched && straightDep < guarantee) {
        switched = true; recoveryBase = book;
      }
      dep = switched ? recoveryBase * recoveryRate : straightDep;
      if (book - dep < 1) dep = book - 1;
      if (y === years) dep = book - 1;
      book = book - dep;
      schedule.push({ year: y, depreciation: dep, bookValue: book });
    }
    return schedule;
  }

  document.getElementById('ds-calc').addEventListener('click', function(){
    var unitPrice = parseInt(document.getElementById('ds-artType').value, 10);
    var qty = parseInt(document.getElementById('ds-qty').value, 10);
    var entity = document.getElementById('ds-entity').value;
    var investment = unitPrice * qty;
    var isImmediate = (unitPrice === 390000);

    var taxRate = 0, income = 0, soleRate = 0;
    if (entity === 'corp') {
      taxRate = parseFloat(document.getElementById('ds-corpRate').value);
    } else {
      income = parseInt(document.getElementById('ds-income').value, 10);
      soleRate = parseFloat(document.getElementById('ds-soleRate').value);
    }

    var schedule = [];
    var warnings = [];

    if (isImmediate) {
      var immediateBase = Math.min(investment, CAP);
      var excess = Math.max(0, investment - CAP);
      if (excess > 0) {
        var excessSched = depreciationSchedule(excess);
        schedule = excessSched.map(function(s, i){
          return {
            year: s.year,
            depreciation: i === 0 ? immediateBase + s.depreciation : s.depreciation,
            bookValue: i === 0 ? excess - s.depreciation : s.bookValue
          };
        });
        warnings.push('投資額が年間300万円を超えています。超過分は即時償却の対象外となり、法定耐用年数8年（定率法）で償却されます。グラフは超過分の年次償却を反映しています。');
      } else {
        schedule = [{ year: 1, depreciation: investment, bookValue: 0 }];
      }
    } else {
      schedule = depreciationSchedule(investment);
    }

    var totalTaxSaving = 0;
    schedule.forEach(function(s){
      var dep = s.depreciation;
      var save = 0;
      if (entity === 'corp') {
        save = dep * taxRate;
      } else {
        var taxableAfter = Math.max(0, income - dep);
        var itSave = incomeTax(income) - incomeTax(taxableAfter);
        var fukko = itSave * 0.021;
        var resSave = Math.min(dep, income) * soleRate;
        save = itSave + fukko + resSave;
        if (dep > income) {
          var w = '経費額が課税所得を上回る年があります（' + s.year + '年目）。課税所得を超える分はその年の節税には使えません。';
          if (warnings.indexOf(w) === -1) warnings.push(w);
        }
      }
      s.taxSaving = save;
      totalTaxSaving += save;
    });

    var maxDep = Math.max.apply(null, schedule.map(function(s){ return s.depreciation; }));
    var chartHtml = schedule.map(function(s){
      var pct = (s.depreciation / maxDep) * 100;
      return '<div class="sim-chart-row">' +
        '<span class="sim-chart-y">' + s.year + '年</span>' +
        '<div class="sim-chart-bar-wrap"><div class="sim-chart-bar" style="width:' + pct.toFixed(1) + '%;"></div></div>' +
        '<span class="sim-chart-val">' + fmtMan(s.depreciation) + '円</span>' +
        '</div>';
    }).join('');

    var tableRows = schedule.map(function(s){
      return '<tr>' +
        '<td>' + s.year + '年目</td>' +
        '<td>' + fmt(s.depreciation) + '円</td>' +
        '<td>' + fmt(s.bookValue) + '円</td>' +
        '<td>' + fmt(s.taxSaving) + '円</td>' +
        '</tr>';
    }).join('');

    var completeYears = schedule.filter(function(s){ return s.depreciation > 0; }).length;
    var warnHtml = warnings.map(function(w){ return '<div class="sim-warning">' + w + '</div>'; }).join('');

    document.getElementById('ds-result').innerHTML =
      '<div class="sim-result-h">試算結果</div>' +
      '<div class="sim-result-row"><span class="sim-result-label">投資額</span><span class="sim-result-value">' + fmt(investment) + '円</span></div>' +
      '<div class="sim-result-row"><span class="sim-result-label">償却完了</span><span class="sim-result-value">' + completeYears + '年</span></div>' +
      '<div class="sim-result-row is-final"><span class="sim-result-label">総税金圧縮額</span><span class="sim-result-value">' + fmtMan(totalTaxSaving) + '円</span></div>' +
      '<div class="sim-chart">' + chartHtml + '</div>' +
      '<table class="sim-table">' +
        '<thead><tr><th>年</th><th>経費計上額</th><th>期末帳簿価額</th><th>税金圧縮額</th></tr></thead>' +
        '<tbody>' + tableRows + '</tbody>' +
      '</table>' +
      warnHtml +
      '<div class="sim-notice">即時償却（少額減価償却資産の特例）の対象は、資本金1億円以下・青色申告・常時使用従業員数400人以下の中小企業者等です。減価償却タイプはこの要件を問わず減価償却資産として扱えます。</div>';
  });
})();
