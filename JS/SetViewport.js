/* ES5 */
function Viewport(setting) {
    const defContent = 'width=device-width,initial-scale=1';
    const CreateMeta = () => {
        let meta = document.querySelector('meta[name="viewport"]');
        if (typeof meta != 'undefined') {
            return;
        }
        meta = document.createElement('meta');
        meta.name = 'viewport';
        meta.content = defContent;
        document.head.appendChild(meta);
    }

    const SetViewport = (setting) => {
        const meta = document.querySelector('meta[name="viewport"]');
        let content = defContent;
        Object.entries(setting).forEach(entry => {
            const [width, value] = entry;
            if (screen.width >= value[0] && screen.width < value[1]) {
                content = 'user-scalable=no,width=' + width;
            }
        });
        meta.setAttribute('content', content);
    }

    CreateMeta();
    SetViewport(setting);
    window.addEventListener('resize', () => {
        SetViewport(setting);
    })
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
        this.defContent = 'width=device-width,initial-scale=1';
        const THIS = this;
        THIS.CreateMeta();
        THIS.SetViewport(setting);
        window.addEventListener('resize', () => {
            THIS.SetViewport(setting);
        })
    }

    CreateMeta() {
        let meta = document.querySelector('meta[name="viewport"]');
        if (typeof meta != 'undefined') {
            return;
        }
        meta = document.createElement('meta');
        meta.name = 'viewport';
        meta.content = this.defContent;
        document.head.appendChild(meta);
    }

    SetViewport(setting) {
        const meta = document.querySelector('meta[name="viewport"]');
        let content = this.defContent;
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