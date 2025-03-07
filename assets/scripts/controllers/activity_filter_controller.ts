// activity_filter_controller.ts
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['activityItem'];
    activityItems!: NodeListOf<HTMLElement>;
    internalCheckbox!: HTMLInputElement;
    externalCheckbox!: HTMLInputElement;

    connect() {
        this.activityItems = this.activityItemTargets;
        this.internalCheckbox = document.getElementById('toggle-internal') as HTMLInputElement;
        this.externalCheckbox = document.getElementById('toggle-external') as HTMLInputElement;
    }

    toggle() {
        const showInternal = this.internalCheckbox.checked;
        const showExternal = this.externalCheckbox.checked;
        const noFilterApplied = !showInternal && !showExternal;

        this.activityItems.forEach(item => {
            const isInternal = item.dataset.isInternal === 'true';
            const isExternal = item.dataset.isExternal === 'true';
            let shouldDisplay = noFilterApplied || (showInternal && isInternal) || (showExternal && isExternal);
            item.style.display = shouldDisplay ? 'block' : 'none';
        });
    }
}