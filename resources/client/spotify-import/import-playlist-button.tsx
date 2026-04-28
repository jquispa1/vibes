import React from 'react';
import {DialogTrigger} from '@ui/overlays/dialog/dialog-trigger';
import {DataTableAddItemButton} from '@common/datatable/data-table-add-item-button';
import {ImportPlaylistDialog} from './import-playlist-dialog';
import {Trans} from '@ui/i18n/trans';

export function ImportPlaylistButton() {
  return (
    <DialogTrigger type="modal">
      <DataTableAddItemButton>
        <Trans message="Import Spotify playlist" />
      </DataTableAddItemButton>
      <ImportPlaylistDialog />
    </DialogTrigger>
  );
}
