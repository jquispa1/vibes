// @ts-nocheck

import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {ImportPlaylistButton} from './import-playlist-button';
import {Trans} from '@ui/i18n/trans';

export function SpotifyImportPage() {
  return (
    <div className="flex min-h-screen flex-col bg">
      <Navbar menuPosition="custom-page-navbar" className="sticky top-0 flex-shrink-0" />
      <div className="flex-auto">
        <div className="mx-auto max-w-850 py-80 px-16 md:px-24">
          <h1 className="text-4xl font-bold">
            <Trans message="Import Spotify playlist" />
          </h1>
          <p className="mt-6 text-muted">
            <Trans message="Paste public Spotify playlist URL or ID. You must be logged in." />
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
