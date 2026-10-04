"use client";

import { useCallback, useEffect, useRef, useState } from "react";

/* Types minimaux de la Web Speech API (absents de lib.dom). */
type SpeechAlternative = { transcript: string };
type SpeechResult = { isFinal: boolean; 0: SpeechAlternative };
type SpeechEvent = { resultIndex: number; results: ArrayLike<SpeechResult> };
type SpeechErrorEvent = { error: string };
interface SpeechRecognitionLike {
  lang: string;
  continuous: boolean;
  interimResults: boolean;
  onresult: ((e: SpeechEvent) => void) | null;
  onerror: ((e: SpeechErrorEvent) => void) | null;
  onend: (() => void) | null;
  start(): void;
  stop(): void;
  abort(): void;
}
type SpeechCtor = new () => SpeechRecognitionLike;

function getSpeechCtor(): SpeechCtor | null {
  if (typeof window === "undefined") return null;
  const w = window as unknown as { SpeechRecognition?: SpeechCtor; webkitSpeechRecognition?: SpeechCtor };
  return w.SpeechRecognition ?? w.webkitSpeechRecognition ?? null;
}

const errorMessages: Record<string, string> = {
  "not-allowed": "Accès au micro refusé. Autorisez le micro dans le navigateur (icône à gauche de l'adresse).",
  "service-not-allowed": "La reconnaissance vocale est bloquée par le navigateur.",
  "audio-capture": "Aucun micro détecté.",
  network: "La reconnaissance vocale a besoin d'internet. Vérifiez la connexion.",
  "language-not-supported": "Le français n'est pas disponible pour la reconnaissance vocale sur cet appareil.",
};

export const BAR_COUNT = 32;

/**
 * Dictée en français : transcription en direct (Web Speech API) + niveau sonore réel du micro.
 * `levels` est un tableau de 0 à 1, mis à jour à chaque image pendant l'écoute.
 */
export function useDictation({ lang = "fr-FR", onEnd }: { lang?: string; onEnd?: (transcript: string, error: string | null) => void } = {}) {
  const [supported, setSupported] = useState<boolean | null>(null);
  const [listening, setListening] = useState(false);
  const [finalText, setFinalText] = useState("");
  const [interim, setInterim] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [levels, setLevels] = useState<number[]>(() => Array(BAR_COUNT).fill(0));

  const recognition = useRef<SpeechRecognitionLike | null>(null);
  const wantListening = useRef(false);
  const interimRef = useRef("");
  const finalRef = useRef("");
  const errorRef = useRef<string | null>(null);
  const onEndRef = useRef(onEnd);
  useEffect(() => {
    onEndRef.current = onEnd;
  });
  const audio = useRef<{ ctx: AudioContext; stream: MediaStream; raf: number } | null>(null);

  useEffect(() => {
    // détection côté client uniquement, après l'hydratation
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setSupported(getSpeechCtor() !== null);
  }, []);

  const stopMeter = useCallback(() => {
    const a = audio.current;
    if (!a) return;
    cancelAnimationFrame(a.raf);
    a.stream.getTracks().forEach((track) => track.stop());
    void a.ctx.close();
    audio.current = null;
    setLevels(Array(BAR_COUNT).fill(0));
  }, []);

  const startMeter = useCallback(async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
      const ctx = new AudioContext();
      const analyser = ctx.createAnalyser();
      analyser.fftSize = 128;
      analyser.smoothingTimeConstant = 0.75;
      ctx.createMediaStreamSource(stream).connect(analyser);
      const data = new Uint8Array(analyser.frequencyBinCount);
      const tick = () => {
        analyser.getByteFrequencyData(data);
        // on garde les fréquences de la voix (bas du spectre), en miroir pour un rendu centré
        const half = BAR_COUNT / 2;
        const side = Array.from({ length: half }, (_, i) => Math.min(1, (data[i + 1] ?? 0) / 200));
        setLevels([...side.slice().reverse(), ...side]);
        if (audio.current) audio.current.raf = requestAnimationFrame(tick);
      };
      audio.current = { ctx, stream, raf: requestAnimationFrame(tick) };
    } catch {
      // le niveau sonore est un bonus : la transcription continue sans lui
    }
  }, []);

  const start = useCallback(() => {
    const Ctor = getSpeechCtor();
    if (!Ctor) return;
    setError(null);
    errorRef.current = null;
    finalRef.current = "";
    setFinalText("");
    setInterim("");
    interimRef.current = "";

    const rec = new Ctor();
    rec.lang = lang;
    rec.continuous = true;
    rec.interimResults = true;
    rec.onresult = (e) => {
      let done = "";
      let live = "";
      for (let i = e.resultIndex; i < e.results.length; i++) {
        const r = e.results[i];
        if (r.isFinal) done += r[0].transcript;
        else live += r[0].transcript;
      }
      if (done) {
        finalRef.current = `${finalRef.current} ${done}`.replace(/\s+/g, " ").trim();
        setFinalText(finalRef.current);
      }
      interimRef.current = live;
      setInterim(live);
    };
    rec.onerror = (e) => {
      if (e.error === "no-speech" || e.error === "aborted") return;
      errorRef.current = errorMessages[e.error] ?? `Erreur de reconnaissance vocale (${e.error}).`;
      setError(errorRef.current);
      wantListening.current = false;
    };
    rec.onend = () => {
      // Chrome coupe après un silence : on relance tant que l'utilisateur n'a pas arrêté
      if (wantListening.current) {
        try {
          rec.start();
          return;
        } catch {
          /* déjà relancée */
        }
      }
      // un résultat encore provisoire à l'arrêt est conservé
      const pending = interimRef.current;
      interimRef.current = "";
      finalRef.current = `${finalRef.current} ${pending}`.replace(/\s+/g, " ").trim();
      setFinalText(finalRef.current);
      setInterim("");
      setListening(false);
      stopMeter();
      onEndRef.current?.(finalRef.current, errorRef.current);
    };

    recognition.current = rec;
    wantListening.current = true;
    try {
      rec.start();
      setListening(true);
      void startMeter();
    } catch {
      setError("Impossible de démarrer le micro.");
    }
  }, [lang, startMeter, stopMeter]);

  const stop = useCallback(() => {
    wantListening.current = false;
    recognition.current?.stop();
  }, []);

  useEffect(
    () => () => {
      wantListening.current = false;
      recognition.current?.abort();
      stopMeter();
    },
    [stopMeter],
  );

  const transcript = `${finalText} ${interim}`.replace(/\s+/g, " ").trim();
  return { supported, listening, transcript, finalText, interim, error, levels, start, stop };
}
