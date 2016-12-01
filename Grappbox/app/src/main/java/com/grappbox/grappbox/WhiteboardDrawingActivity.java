package com.grappbox.grappbox;

import android.content.Intent;
import android.os.Bundle;
import android.app.Activity;

import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

public class WhiteboardDrawingActivity extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_whiteboard_drawing);
        getActionBar().setDisplayHomeAsUpEnabled(true);
        Intent open = new Intent(this, GrappboxWhiteboardJIT.class);
        //TODO : link to service
    }

}
