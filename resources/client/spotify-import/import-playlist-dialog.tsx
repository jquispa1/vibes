import {Dialog} from '@ui/overlays/dialog/dialog';
import {DialogHeader} from '@ui/overlays/dialog/dialog-header';
import {Trans} from '@ui/i18n/trans';
import {DialogBody} from '@ui/overlays/dialog/dialog-body';
import {useForm} from 'react-hook-form';
import {Form} from '@ui/forms/form';
import {useDialogContext} from '@ui/overlays/dialog/dialog-context';
import {FormTextField} from '@ui/forms/input-field/text-field/text-field';
import {DialogFooter} from '@ui/overlays/dialog/dialog-footer';
import {Button} from '@ui/buttons/button';
import {useImportPlaylist} from './requests/use-import-playlist';

export function ImportPlaylistDialog() {
  const {close, formId} = useDialogContext();
  const form = useForm<{url: string}>({defaultValues: {url: ''}});
  const importPlaylist = useImportPlaylist();

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Import Spotify playlist" />
      </DialogHeader>
      <DialogBody>
        <Form
          id={formId}
          form={form}
          onSubmit={values => {
            importPlaylist.mutate(values, {
              onSuccess: response => {
                close(response.playlist);
              },
            });
          }}
        >
          <div className="space-y-12">
            <div>
              <FormTextField
                autoFocus
                required
                name="url"
                label={<Trans message="Playlist URL or ID" />}
                placeholder="https://open.spotify.com/playlist/..."
              />
              <p className="mt-8 text-sm text-muted">
                <Trans message="Paste a public playlist URL to import it into your account." />
              </p>
            </div>
          </div>
        </Form>
      </DialogBody>
      <DialogFooter>
        <Button onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button
          form={formId}
          variant="flat"
          color="primary"
          type="submit"
          disabled={importPlaylist.isPending}
        >
          <Trans message="Import" />
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
