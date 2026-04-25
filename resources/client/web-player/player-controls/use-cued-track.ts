import {usePlayerStore} from '@common/player/hooks/use-player-store';
import {Track} from '@app/web-player/tracks/track';
import {useEffect, useRef} from 'react';

export function useCuedTrack(): Track | undefined {
  const cuedMedia = usePlayerStore((s) => s.cuedMedia);
  const mediaDuration = usePlayerStore((s) => s.mediaDuration); // ⚡ aquí tomamos duration real
  const lastCuedMediaIdRef = useRef<string | number | null>(null);

  useEffect(() => {
    if (!cuedMedia) return;

    if (lastCuedMediaIdRef.current === cuedMedia.id) return;
    lastCuedMediaIdRef.current = cuedMedia.id;

    const meta: any = cuedMedia.meta || {};
    const title = meta.name ?? meta.title ?? '';
    const artist = meta.artists?.[0]?.name ?? meta.artist ?? '';
    const artworkUrl = meta.image ?? meta.album?.image ?? cuedMedia.poster ?? '';
    const duration = Math.floor(mediaDuration || 0); // ✅ aquí usamos mediaDuration del store
    const videoId =
      cuedMedia.provider === 'youtube' &&
      typeof (cuedMedia as any).src === 'string' &&
      (cuedMedia as any).src !== 'resolve'
        ? (cuedMedia as any).src
        : `${cuedMedia.id}`;

    const android = (window as any).Android;
    console.log('Cued track changed:', {videoId, title, artist, artworkUrl, duration});

    if (android && typeof android.notifySongChanged === 'function') {
      try {
        android.notifySongChanged(videoId, title, artist, artworkUrl, duration);
        console.log('Song change notified to Android');
      } catch (e) {
        console.warn('Error notifying Android:', e);
      }
    }
  }, [cuedMedia, mediaDuration]);

  if (!cuedMedia) return;
  return cuedMedia.meta as Track;
}
