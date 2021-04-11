import Model from 'flarum/common/Model';

/**
 * Same as calling JSON.stringify on Model.save's attributes but works around cyclic references
 */
export default function (attributes) {
    const data = {
        attributes: {
            // Clone attributes to prevent modifying original object
            ...attributes,
        },
    };

    // Same as Model.save does on its relationships
    if (data.attributes.relationships) {
        data.relationships = {};

        for (const key in data.attributes.relationships) {
            if (!data.attributes.relationships.hasOwnProperty(key)) {
                continue;
            }

            const model = data.attributes.relationships[key];

            data.relationships[key] = {
                data: model instanceof Array ? model.map(Model.getIdentifier) : Model.getIdentifier(model),
            };
        }

        delete data.attributes.relationships;
    }

    return JSON.stringify(data);
}
