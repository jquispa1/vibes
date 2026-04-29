import {useDialogContext} from '@ui/overlays/dialog/dialog-context';
import {DialogBody} from '@ui/overlays/dialog/dialog-body';
import {DialogFooter} from '@ui/overlays/dialog/dialog-footer';
import {Dialog} from '@ui/overlays/dialog/dialog';
import {DialogHeader} from '@ui/overlays/dialog/dialog-header';
import {useForm} from 'react-hook-form';
import {Trans} from '@ui/i18n/trans';
import {Button} from '@ui/buttons/button';
import {Input} from '@ui/forms/input-field/input';
import {useState} from 'react';

export function ImportPlaylistDialog() {
  const {close, formId} = useDialogContext();
  const form = useForm<{url: string}>({defaultValues: {url: ''}});
  const [isSubmitting, setIsSubmitting] = useState(false);

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Import Spotify playlist" />
      </DialogHeader>
      <DialogBody>
        <form
          id={formId}
          method="GET"
          action="/spotify/import/authorize"
          onSubmit={() => setIsSubmitting(true)}
        >
          <div className="space-y-12">
            <div>
              <label className="mb-8 block text-sm font-medium text-muted"><Trans message="Playlist URL or ID" /></label>
              <Input>
                <input
                  {...form.register('url', {required: true})}
                  placeholder="https://open.spotify.com/playlist/..."
                />
              </Input>
              <p className="mt-8 text-sm text-muted">
                <Trans message="Spotify will ask you to authorize access to this playlist." />
              </p>
            </div>
          </div>
        </form>
      </DialogBody>
      <DialogFooter>
        <Button onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button form={formId} disabled={isSubmitting} variant="flat" color="primary" type="submit">
          {isSubmitting ? <Trans message="Redirecting..." /> : <Trans message="Import" />}
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
