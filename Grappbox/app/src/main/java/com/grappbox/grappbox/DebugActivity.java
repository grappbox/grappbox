package com.grappbox.grappbox;

import android.os.Bundle;
import android.app.Activity;
import android.widget.TextView;

public class DebugActivity extends Activity {
    public static final String EXTRA_DEBUG = "debug";
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_debug);
        if (getIntent() != null){
            ((TextView) findViewById(R.id.debugjson)).setText(getIntent().getStringExtra(EXTRA_DEBUG));
        }
    }


}
