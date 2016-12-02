package com.grappbox.grappbox;

import android.content.Intent;
import android.os.Bundle;
import android.app.Activity;

import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.receiver.WhiteboardListReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;
import com.grappbox.grappbox.views.Whiteboard;

import java.util.ArrayList;
import java.util.List;

public class WhiteboardDrawingActivity extends Activity implements WhiteboardListReceiver.Callback {
    public static final String EXTRA_WHITEBOARD_ID = "com.grappbox.extra.whiteboard_id";

    public interface ResultDispatcher{
        void onOpen(List<WhiteboardModel> result);
    }

    private List<ResultDispatcher> mListeners;

    public void registerObserver(ResultDispatcher callback){
        if (!mListeners.contains(callback))
            mListeners.add(callback);
    }

    public void unregisterObserver(ResultDispatcher callback){
        mListeners.remove(callback);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_whiteboard_drawing);
        getActionBar().setDisplayHomeAsUpEnabled(true);
        mListeners = new ArrayList<>();
        Intent open = new Intent(this, GrappboxWhiteboardJIT.class);
        open.setAction(GrappboxWhiteboardJIT.ACTION_OPEN);
        open.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, getIntent().getStringExtra(EXTRA_WHITEBOARD_ID));
        open.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, new WhiteboardListReceiver(this));
        startService(open);
    }

    @Override
    public void onListReceived(List<WhiteboardModel> models) {
        for (ResultDispatcher listener : mListeners) {
            listener.onOpen(models);
        }
    }

}
