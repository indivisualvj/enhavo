import { AbstractAction } from "@enhavo/app/action/model/AbstractAction";
import {ResourceInputManager, InputChangedEvent} from "@enhavo/app/manager/ResourceInputManager";
import {FrameManager} from "@enhavo/app/frame/FrameManager";
import {UiManager} from "@enhavo/app/ui/UiManager";
import {FlashMessenger} from "@enhavo/app/flash-message/FlashMessenger";
import {Translator} from "@enhavo/app/translation/Translator";
import {ClientInterface} from "@enhavo/app/client/ClientInterface";
import {ActionInterface} from "../ActionInterface";

export class SaveAction extends AbstractAction
{
    public url: string;

    /**
     * Morphing the form after save keeps the scroll position, but it also resets the
     * state of tree like forms. Named morphOnSave, because morph() is the method the
     * action manager uses to update the action itself.
     */
    public morphOnSave: boolean = true;

    public confirm: boolean = false;
    public confirmMessage: string;
    public confirmLabelOk: string;
    public confirmLabelCancel: string;

    constructor(
        private readonly frameManager: FrameManager,
        private readonly uiManager: UiManager,
        private readonly resourceInputManager: ResourceInputManager,
        private readonly flashMessenger: FlashMessenger,
        private readonly translator: Translator,
        private readonly client: ClientInterface,
    ) {
        super();
    }

    async execute(): Promise<void>
    {
        if (this.confirm) {
            const accept = await this.uiManager.confirm({
                message: this.confirmMessage,
                denyLabel: this.confirmLabelCancel,
                acceptLabel: this.confirmLabelOk,
            });

            if (!accept) {
                return;
            }
        }

        await this.save();
    }

    private async save(): Promise<void>
    {
        this.uiManager.loading(true);

        const transport = await this.resourceInputManager.save(this.url, this.morphOnSave);
        this.uiManager.loading(false);

        if (!transport.ok || !transport.response.ok) {
            await this.client.handleError(transport, {
                confirm: true,
                validation: true,
            });
            return;
        }

        this.flashMessenger.add(this.translator.trans('enhavo_app.input.message.save_success', {}, 'javascript'));
        this.frameManager.dispatch(new InputChangedEvent(this.resourceInputManager.resource));
    }

    morph(source: SaveAction)
    {
        this.url = source.url;
        this.morphOnSave = source.morphOnSave;
        this.confirm = source.confirm;
        this.confirmMessage = source.confirmMessage;
        this.confirmLabelOk = source.confirmLabelOk;
        this.confirmLabelCancel = source.confirmLabelCancel;
    }
}