import {useMutation} from '@tanstack/react-query';
import {apiClient} from '@common/http/query-client';

interface ImportPayload {
  url: string;
}

export interface ImportPlaylistResponse {
  playlist: unknown;
  attached: number;
  total: number;
}

export function useImportPlaylist() {
  return useMutation<ImportPlaylistResponse, Error, ImportPayload>({
    mutationFn: async (payload: ImportPayload) => {
      const response = await apiClient.post('spotify/import-playlist', payload);
      return response.data as ImportPlaylistResponse;
    },
  });
}
