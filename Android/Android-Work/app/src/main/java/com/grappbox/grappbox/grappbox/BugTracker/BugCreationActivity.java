package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.Activity;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.Button;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.R;

public class BugCreationActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_bug_creation);

        LinearLayout tagList = (LinearLayout) findViewById(R.id.ll_categories);
        LinearLayout userList = (LinearLayout) findViewById(R.id.ll_assignee);
        Button save = (Button) findViewById(R.id.btn_save);

        GetBugTagTask bttask = new GetBugTagTask(this, tagList, null);
        bttask.execute();
        GetProjectUserTask puttask = new GetProjectUserTask(this, userList, null);
        puttask.execute();
        Activity me = this;
        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                LinearLayout rootlayout = (LinearLayout) findViewById(R.id.ll_root);
                CreateBugTask cbtask = new CreateBugTask(me, rootlayout);
                cbtask.execute();
            }
        });
    }
}
