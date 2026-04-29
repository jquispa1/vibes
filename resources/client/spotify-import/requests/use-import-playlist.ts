import {useMutation} from '@tanstack/react-query';
import {apiClient, queryClient} from '@common/http/query-client';
import {toast} from '@ui/toast/toast';
import {useTrans} from '@ui/i18n/use-trans';
import {message} from '@ui/i18n/message';
import {showHttpErrorToast} from '@common/http/show-http-error-toast';
import {useAuth} from '@common/auth/use-auth';

interface ImportPayload {
  url: string;
}

export interface ImportPlaylistResponse {
  playlist: unknown;
  attached: number;
  total: number;
}

export function useImportPlaylist() {
  const {trans} = useTrans();
  const {user} = useAuth();
  return useMutation<ImportPlaylistResponse, Error, ImportPayload>({
    mutationFn: async (payload: ImportPayload) => {
      const response = await apiClient.post('spotify/import-playlist', payload);
      return response.data as ImportPlaylistResponse;
    },
    onSuccess: () => {
      toast(trans(message('Playlist imported')));
      queryClient.invalidateQueries({queryKey: ['playlists', 'library']});
      if (user?.id) {
        queryClient.invalidateQueries({queryKey: ['playlists', 'library', user.id]});
      }
    },
    onError: err => showHttpErrorToast(err),
  });
}
