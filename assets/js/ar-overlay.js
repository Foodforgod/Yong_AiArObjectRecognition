class AROverlayManager {
    constructor(containerElement, boundingBoxElement, cardElement) {
        this.container = containerElement;
        this.boundingBox = boundingBoxElement;
        this.card = cardElement;
        this.expanded = false;

        this.card.querySelector('.ar-info-header').addEventListener('click', () => {
            this.toggleExpand();
        });
    }

    show(data) {
        const box = data.boundingBox;
        // Convert 0-1000 normalized coordinates to percentages
        const top = (box.ymin / 1000) * 100;
        const left = (box.xmin / 1000) * 100;
        const height = ((box.ymax - box.ymin) / 1000) * 100;
        const width = ((box.xmax - box.xmin) / 1000) * 100;

        this.boundingBox.style.top = `${top}%`;
        this.boundingBox.style.left = `${left}%`;
        this.boundingBox.style.height = `${height}%`;
        this.boundingBox.style.width = `${width}%`;
        this.boundingBox.style.display = 'block';

        // Populate Card
        this.card.querySelector('.ar-object-label').textContent = data.objectLabel;
        this.card.querySelector('.ar-product-title').textContent = data.productName;
        this.card.querySelector('.spec-text').textContent = data.specification;
        this.card.querySelector('.desc-text').textContent = data.description;
        this.card.querySelector('.mfg-text').textContent = data.manufacturer;
        this.card.querySelector('.conf-text').textContent = `${Math.round(data.confidence * 100)}% (${data.provider})`;

        this.card.style.display = 'block';
        this.expanded = false;
        this.card.querySelector('.ar-details-body').style.display = 'none';
    }

    toggleExpand() {
        this.expanded = !this.expanded;
        const body = this.card.querySelector('.ar-details-body');
        body.style.display = this.expanded ? 'block' : 'none';
    }

    hide() {
        this.boundingBox.style.display = 'none';
        this.card.style.display = 'none';
    }
}