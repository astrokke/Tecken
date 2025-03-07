export default class DomManager {
    /**
    * Crée un élément du dom, lui ajoute du texte, le place comme dernier
    * enfant de parent et ajoute un attribut en utilisant le paramètre attributes
    */
    createHtml(
        tag_name: string,
        text_content: string,
        parent: HTMLElement,
        attributes: Object[] = [],
    ): HTMLElement {
        const html_tag = document.createElement(tag_name);
        html_tag.textContent = text_content;
        parent.appendChild(html_tag);
        for (const attribute of attributes) {
            for (let key in attribute) {
                html_tag.setAttribute(key, attribute[key]);
            }
        }
        return html_tag;
    }
}
