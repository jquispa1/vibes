// @ts-nocheck

import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {ImportPlaylistButton} from './import-playlist-button';
import {Trans} from '@ui/i18n/trans';
import {useSearchParams} from 'react-router';

export function SpotifyImportPage() {
  const [searchParams] = useSearchParams();
  const status = searchParams.get('spotify_import');
  const error = searchParams.get('spotify_import_error');
  const attached = searchParams.get('attached');
  const total = searchParams.get('total');

  return (
    <div className="flex min-h-screen flex-col bg">
      <Navbar menuPosition="custom-page-navbar" className="sticky top-0 flex-shrink-0" />
      <div className="flex-auto">
        <div className="mx-auto max-w-850 py-80 px-16 md:px-24">
          {status === 'success' ? (
            <div className="mb-16 rounded-lg border border-green-500/20 bg-green-500/10 px-12 py-10 text-green-600">
              <Trans message="Playlist imported successfully" />
              {attached && total ? (
                <span className="ml-8 text-muted">
                  {attached}/{total} <Trans message="tracks imported" />
                </span>
              ) : null}
            </div>
          ) : null}

          {error ? (
            <div className="mb-16 rounded-lg border border-red-500/20 bg-red-500/10 px-12 py-10 text-red-600">
              {error}
            </div>
          ) : null}

          <h1 className="text-4xl font-bold">
            <Trans message="Import Spotify playlist" />
          </h1>
          <p className="mt-6 text-muted">
            <Trans message="Paste a Spotify playlist URL or ID. You will be sent to Spotify to approve access." />
          </p>
          <div className="mt-16">
            <ImportPlaylistButton />
          </div>
        </div>
      </div>
      <Footer className="mx-14 md:mx-40" />
    </div>
  );
}
