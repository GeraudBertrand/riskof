var summaries;

window.addEventListener('load', function () {
    summaries = document.querySelectorAll("summary");
    summaries.forEach(summary => {
        summary.addEventListener('click', function () {
            summary.classList.toggle('open');
        });

        summary.addEventListener('click', function(){
            summaries.forEach(s => {
                if (s !== summary) {
                    s.classList.remove('focused');
                }
            });
            if(summary.classList.contains('open')){
                summary.classList.add('focused');
            }
        });
    });
});


