import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';

interface ImportPayload {
  url: string;
}

export function useImportPlaylist() {
  return useMutation((payload: ImportPayload) =>
    apiClient.post('spotify/import-playlist', payload).then(res => res.data),
  );
}
