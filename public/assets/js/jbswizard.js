// Bootstrap ScrollSpy
function initBsScrollSpy(targetSelector,offsetHeight) {
    targetSelector = typeof targetSelector !== 'undefined' ? targetSelector : '#navbar';
    offsetHeight   = typeof offsetHeight !== 'undefined'   ? offsetHeight   : 0;

    $('body').scrollspy({
        target: targetSelector,
        offset: offsetHeight
    });

    // Scroll To
    $(targetSelector).find('a').click(function () {
        var scrollTarget = $(this).attr('href');
        var scrollTargetOffset = $(scrollTarget).offset();
        var scrollPos = scrollTargetOffset.top - offsetHeight;
        $('body, html').animate({
            scrollTop: scrollPos
        }, 500);
        return false;
    });
}

// Example Event Log Methods
function addLogEvent(jBsWizard_exampleElement) {
    jBsWizard_exampleElement.on('jBsWizard.initializing'  , function(e, instance) {
        addLog('jBsWizard.initializing');
    });
    jBsWizard_exampleElement.on('jBsWizard.initialized'   , function(e, instance) {
        addLog('jBsWizard.initialized');
        $('#getWizardSteps').click();
    });
    jBsWizard_exampleElement.on('jBsWizard.destroy'       , function(e, instance) {
        addLog('jBsWizard.destroy');
    });
    jBsWizard_exampleElement.on('jBsWizard.destroyed'     , function(e, instance) {
        addLog('jBsWizard.destroyed');
        $('#example-steps').html('');
    });
    jBsWizard_exampleElement.on('jBsWizard.showProcessing', function(e, instance) {
        addLog('jBsWizard.showProcessing');
    });
    jBsWizard_exampleElement.on('jBsWizard.hideProcessing', function(e, instance) {
        addLog('jBsWizard.hideProcessing');
    });
    jBsWizard_exampleElement.on('jBsWizard.prevStep'      , function(e, instance) {
        addLog('jBsWizard.prevStep');
    });
    jBsWizard_exampleElement.on('jBsWizard.nextStep'      , function(e, instance) {
        addLog('jBsWizard.nextStep');
    });
    jBsWizard_exampleElement.on('jBsWizard.goToStep'      , function(e, instance, steps) {
        addLog('jBsWizard.goToStep');

        var getSteps = JSON.stringify(steps, null, 4);
        $('#example-steps').html(getSteps);
    });
}
function addLog(log) {
    log = typeof log !== 'undefined' ? log : '';

    if(log !== '') {

        var currentDate = new Date();

        var day      = ('0' + currentDate.getDate()).slice(-2);
        var month    = ('0' + currentDate.getMonth()).slice(-2);
        var year     = currentDate.getFullYear();
        var fullDate = year + '-' + month + '-' + day;

        var hours    = ('0' + currentDate.getHours()).slice(-2);
        var minutes  = ('0' + currentDate.getMinutes()).slice(-2);
        var seconds  = ('0' + currentDate.getSeconds()).slice(-2);
        var fullTime = hours + ':' + minutes + ':' + seconds;

        var logStr = fullDate + ' ' + fullTime + ' => ' + log + "\n"

        $('#example-log').append(logStr);
    }
}
function resetLog() {
    $('#example-log').html('');
}

jQuery(function($) {
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]',
        container: 'body',
        viewport: 'body'
    });

    // Bootstrap ScrollSpy
    initBsScrollSpy('#navbar', 70);

    // jBsWizard Example
    var jBsWizard_exampleElement = $('#j-bs-wizard-example');

    // Auto Initialize Example
    var jBsWizard = jBsWizard_exampleElement.jBsWizard();
    addLogEvent(jBsWizard_exampleElement);

    // Example manual Actions
    $('#initWizard').click( function(e) {
        e.preventDefault();

        // Set manual Options
        var options = {};

        var nextBtnStr             = $('.example-options #nextBtnStr').val();
        var nextBtnAdditionalClass = $('.example-options #nextBtnAdditionalClass').val();
        var prevBtnStr             = $('.example-options #prevBtnStr').val();
        var prevBtnAdditionalClass = $('.example-options #prevBtnAdditionalClass').val();
        var waitASecondDelay       = $('.example-options #waitASecondDelay').val();
        var processingFadeSpeed    = $('.example-options #processingFadeSpeed').val();
        var useProgressBar         = $('.example-options #useProgressBar').prop('checked');

        if(nextBtnStr !== '') {
            options.nextBtnStr = nextBtnStr;
        }
        if(nextBtnAdditionalClass !== '') {
            options.nextBtnAdditionalClass = nextBtnAdditionalClass;
        }
        if(prevBtnStr !== '') {
            options.prevBtnStr = prevBtnStr;
        }
        if(prevBtnAdditionalClass !== '') {
            options.prevBtnAdditionalClass = prevBtnAdditionalClass;
        }
        if(waitASecondDelay !== '' && waitASecondDelay === parseInt(waitASecondDelay, 10)) {
            options.waitASecondDelay = waitASecondDelay;
        }
        if(processingFadeSpeed !== '') {
            options.processingFadeSpeed = processingFadeSpeed;
        }
        options.useProgressBar = useProgressBar;

        // Initialise Manual jBsWizard11
        jBsWizard = jBsWizard_exampleElement.jBsWizard(options);
        addLogEvent(jBsWizard_exampleElement);
    });
    $('#destroyWizard').click( function(e) {
        e.preventDefault();
        jBsWizard.destroy();
    });

    $('#showWizardProcessing').click( function(e) {
        e.preventDefault();
        jBsWizard.showProcessing();
    });
    $('#hideWizardProcessing').click( function(e) {
        e.preventDefault();
        jBsWizard.hideProcessing();
    });

    $('#prevWizardStep').click( function(e) {
        e.preventDefault();
        jBsWizard.prevStep();
    });
    $('#nextWizardStep').click( function(e) {
        e.preventDefault();
        jBsWizard.nextStep();
    });
    $('#getWizardSteps').click( function(e) {
        e.preventDefault();
        var steps = JSON.stringify(jBsWizard.steps(), null, 4);
        $('#example-steps').html(steps);
    });

    $('#example-log-reset').click( function(e) {
        e.preventDefault();
        resetLog();
    });
});