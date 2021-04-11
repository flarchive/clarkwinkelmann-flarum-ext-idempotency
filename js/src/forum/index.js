import {extend, override} from 'flarum/common/extend';
import DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import ReplyComposer from 'flarum/forum/components/ReplyComposer';
import Model from 'flarum/common/Model';
import stringifyAttributes from './stringifyAttributes';

function modifyData(data) {
    const newData = stringifyAttributes(data);

    if (this.idempotencyPreviousData !== newData) {
        this.idempotencyPreviousData = newData;

        this.idempotencyKey = Math.random().toString(36).substr(2);
    }

    data.idempotencyKey = this.idempotencyKey;
}

app.initializers.add('clarkwinkelmann-idempotency', () => {
    // We have to extend data() + save() because we cannot insert additional options{} in onsubmit
    extend(DiscussionComposer.prototype, 'data', modifyData);
    extend(ReplyComposer.prototype, 'data', modifyData);

    override(Model.prototype, 'save', function (original, attributes, options = {}) {
        if (!attributes.idempotencyKey) {
            return original(attributes, options);
        }

        const {idempotencyKey, ...attributesWithoutIdempotency} = attributes;

        const optionsWithIdempotency = {...options};

        if (!optionsWithIdempotency.headers) {
            optionsWithIdempotency.headers = {};
        }

        optionsWithIdempotency.headers['Idempotency-Key'] = idempotencyKey;

        return original(attributesWithoutIdempotency, optionsWithIdempotency);
    });
}, -100); // Needs to run after all custom fields in discussion/post composer, including Tags
