
function Viewport(setting) {
    SetViewport(setting);
    window.addEventListener('resize', () => {
        SetViewport(setting);
    })

    function SetViewport(setting) {
        const meta = document.querySelector('meta[name="viewport"]');
        let content = 'width=device-width,initial-scale=1';
        Object.entries(setting).forEach(entry => {
            const [width, value] = entry;
            if (screen.width >= value[0] && screen.width < value[1]) {
                content = 'user-scalable=no,width=' + width;
            }
        });
        meta.setAttribute('content', content);
    }
}

Viewport(
    {
        /* viewport_width: [from_width, to_width] */
        360: [0, 400],
        578: [400, 578],
    }
);


/* ES6 */
class Viewport {
    consructor(setting) {
        const THIS = this;
        THIS.SetViewport(setting);
        window.addEventListener('resize', () => {
            THIS.SetViewport(setting);
        })
    }

    SetViewport(setting) {
        const meta = document.querySelector('meta[name="viewport"]');
        let content = 'width=device-width,initial-scale=1';
        Object.entries(setting).forEach(entry => {
            const [width, value] = entry;
            if (screen.width >= value[0] && screen.width < value[1]) {
                content = 'user-scalable=no,width=' + width;
            }
        });
        meta.setAttribute('content', content);
    }
}
new Viewport(
    {
        /* viewport_width: [from_width, to_width] */
        360: [0, 400],
        578: [400, 578],
    }
)