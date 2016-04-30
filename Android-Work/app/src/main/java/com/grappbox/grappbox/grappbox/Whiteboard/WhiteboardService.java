package com.grappbox.grappbox.grappbox.Whiteboard;

import android.app.IntentService;
import android.app.Service;
import android.content.Intent;
import android.os.IBinder;
import android.widget.Toast;

/**
 * Created by tan_f on 01/04/2016.
 */
public class WhiteboardService extends IntentService {

    WhiteboardService()
    {
        super("WhiteboardService");
    }

    @Override
    protected void onHandleIntent(Intent intent)
    {

    }

    @Override
    public  int onStartCommand(Intent intent, int flags, int startId)
    {

        return super.onStartCommand(intent,flags,startId);
    }

    @Override
    public IBinder onBind(Intent intent)
    {
        return null;
    }
}
