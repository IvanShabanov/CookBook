
function InitViewport(minwidth) {
    SetViewport(minwidth);
    window.addEventListener('resize', () => {
        SetViewport(minwidth);
    })
}

function SetViewport(minwidth) {
    const meta = document.querySelector('meta[name="viewport"]');
    if (screen.width < minwidth) {
        meta.setAttribute('content', 'user-scalable=no,width=' + minwidth);
    } else {
        meta.setAttribute('content', 'width=device-width,initial-scale=1');
    }
}

InitViewport(360);
