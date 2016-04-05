package com.grappbox.grappbox.grappbox.Whiteboard;

import android.app.AlarmManager;
import android.app.IntentService;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.text.format.DateUtils;
import android.text.format.Time;
import android.util.Log;


/**
 * Created by tan_f on 04/04/2016.
 */
public class WhiteboardPullIntentService extends IntentService {

    public WhiteboardPullIntentService (){
        super("WhiteboardPullIntentService");
    }

    @Override
    protected void onHandleIntent(Intent intent)
    {
        //String receive = "Data receive" + intent.getStringExtra("URL");

        Log.v("Handle Service", "true");
        Intent broadcastIntent = new Intent();
        broadcastIntent.setAction(WhiteboardFragment.MyReceiver.ACTION_RESP);
        broadcastIntent.addCategory(Intent.CATEGORY_DEFAULT);
        /*broadcastIntent.putExtra("Test", receive.toString());*/
        sendBroadcast(broadcastIntent);
    }

}
