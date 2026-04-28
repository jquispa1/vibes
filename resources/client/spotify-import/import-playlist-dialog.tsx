import {useDialogContext} from '@ui/overlays/dialog/dialog-context';
import {DialogBody} from '@ui/overlays/dialog/dialog-body';
import {DialogFooter} from '@ui/overlays/dialog/dialog-footer';
import {Dialog} from '@ui/overlays/dialog/dialog';
import {DialogHeader} from '@ui/overlays/dialog/dialog-header';
import {useForm} from 'react-hook-form';
import {Trans} from '@ui/i18n/trans';
import {Button} from '@ui/buttons/button';
import {Input} from '@ui/forms/input-field/input';
import {
  ImportPlaylistResponse,
  useImportPlaylist,
} from './requests/use-import-playlist';
import {useEffect, useState} from 'react';

export function ImportPlaylistDialog() {
  const {close, formId} = useDialogContext();
  const form = useForm<{url: string}>({defaultValues: {url: ''}});
  const importMutation = useImportPlaylist();
  const [result, setResult] = useState<ImportPlaylistResponse | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!result) return;

    const timeout = window.setTimeout(() => close(), 1800);
    return () => window.clearTimeout(timeout);
  }, [close, result]);

  return (
    <Dialog>
      <DialogHeader>
        <Trans message="Import Spotify playlist" />
      </DialogHeader>
      <DialogBody>
        <form id={formId} onSubmit={form.handleSubmit(values => {
          setError(null);
          setResult(null);
          importMutation.mutate(values, {
            onSuccess: (data) => {
              setResult(data);
            },
            onError: (err: any) => {
              setError(err?.response?.data?.message || err?.message || 'Import failed');
            },
          });
        })}>
          <div className="space-y-12">
            <div>
              <label className="mb-8 block text-sm font-medium text-muted"><Trans message="Playlist URL or ID (must be public)" /></label>
              <Input disabled={importMutation.isPending}>
                <input
                  {...form.register('url', {required: true})}
                  placeholder="https://open.spotify.com/playlist/..."
                />
              </Input>
              <p className="mt-8 text-sm text-muted">
                <Trans message="Example: https://open.spotify.com/playlist/{id}" />
              </p>

              {importMutation.isPending && (
                <div className="mt-12">
                  <div className="h-2 w-full overflow-hidden rounded bg-gray-200">
                    <div className="h-2 w-1/3 animate-pulse rounded bg-primary" />
                  </div>
                  <p className="mt-6 text-sm">
                    <Trans message="Importing..." />
                  </p>
                </div>
              )}

              {result && (
                <div className="mt-12 rounded-lg border border-green-500/20 bg-green-500/10 px-12 py-10">
                  <p className="text-sm font-medium text-green-600">
                    <Trans message="Playlist imported successfully" />
                  </p>
                  <p className="text-sm text-muted mt-4">
                    {result.attached}/{result.total} <Trans message="tracks imported" />
                  </p>
                </div>
              )}

              {error && (
                <div className="mt-12 rounded-lg border border-red-500/20 bg-red-500/10 px-12 py-10 text-sm text-red-600">
                  {error}
                </div>
              )}
            </div>
          </div>
        </form>
      </DialogBody>
      <DialogFooter>
        <Button onClick={() => close()}>
          <Trans message="Cancel" />
        </Button>
        <Button form={formId} disabled={importMutation.isPending} variant="flat" color="primary" type="submit">
          {importMutation.isPending ? <Trans message="Importing..." /> : <Trans message="Import" />}
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
