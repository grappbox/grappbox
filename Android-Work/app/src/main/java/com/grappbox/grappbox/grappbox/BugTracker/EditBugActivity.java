package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

public class EditBugActivity extends AppCompatActivity {
    public static final String EXTRA_GRAPPBOX_BUG_ID = "extra.grappbox.bugId";

    private BugEntity _bug;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        EditBugActivity me = this;
        Intent intent = getIntent();

        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_edit_bug);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        if (intent == null)
        {
            onBackPressed();
            return;
        }
        String bugId = intent.getStringExtra(EXTRA_GRAPPBOX_BUG_ID);
        TextView title, description;
        title = (TextView) findViewById(R.id.et_title);
        description = (TextView) findViewById(R.id.et_description);

        if (bugId == null || bugId.isEmpty())
            onBackPressed();
        GetTicketTask task = new GetTicketTask(this, new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {

                if (isErrorOccured || params.length < 1) {
                    onBackPressed();
                    return;
                }
                try {
                    JSONObject data = new JSONObject(params[0]);
                    _bug = new BugEntity(data);
                    title.setText(_bug.GetTitle());
                    description.setText(_bug.GetDescription());

                    TextView status = (TextView) findViewById(R.id.txt_status);
                    TextView owner = (TextView) findViewById(R.id.txt_owner);
                    Button btn_close = (Button) findViewById(R.id.btn_close);
                    String strStatus = "Status : " + (_bug.IsClosed() ? "Closed" : "Open");
                    String strOwner = "by " + _bug.GetCreatorFullname();

                    status.setText(strStatus);
                    owner.setText(strOwner);

                    if (_bug.GetCreatorId().equals(SessionAdapter.getInstance().getUserID()))
                    {
                        Button btn_save = (Button) findViewById(R.id.btn_save);

                        title.setEnabled(true);
                        description.setEnabled(true);
                        btn_save.setVisibility(View.VISIBLE);

                        btn_save.setOnClickListener(new View.OnClickListener() {
                            @Override
                            public void onClick(View v) {
                                EditTicketTask task = new EditTicketTask(getParent(), new OnTaskListener() {
                                    @Override
                                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                        if (!isErrorOccured && params.length > 0) {
                                            try {
                                                _bug.reimport(new JSONObject(params[0]));
                                                AlertDialog.Builder builder = new AlertDialog.Builder(me, R.style.AppTheme);
                                                builder.setTitle(getString(R.string.bug_modif_success));
                                                builder.setMessage(getString(R.string.bug_modif_desc_success));
                                                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                                                    @Override
                                                    public void onClick(DialogInterface dialog, int which) {
                                                        dialog.dismiss();
                                                    }
                                                });
                                                builder.setNegativeButton(null, null);
                                                builder.show();
                                            } catch (JSONException e) {
                                                e.printStackTrace();
                                            }
                                        }
                                    }
                                });

                                task.execute(_bug.GetId(), title.getText().toString(), description.getText().toString());
                            }
                        });
                    }

                    btn_close = (Button) findViewById(R.id.btn_close);
                    if (_bug.IsClosed()) {
                        btn_close.setText("Re Open Ticket");
                        btn_close.setBackgroundColor(ContextCompat.getColor(getParent(), R.color.colorGrappboxGreen));
                    }
                    btn_close.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            if(_bug.IsClosed()) {
                                CloseBugTask task = new CloseBugTask(getParent(), new OnTaskListener() {
                                    @Override
                                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                        if (!isErrorOccured)
                                            onBackPressed();
                                    }
                                });
                                task.execute(_bug.GetId());
                            }
                            else
                            {
                                ReopenTicketTask task = new ReopenTicketTask(getParent(), new OnTaskListener() {
                                    @Override
                                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                        if (!isErrorOccured)
                                            onBackPressed();
                                    }
                                });
                                task.execute(_bug.GetId());
                            }
                        }
                    });
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        task.execute(bugId);
        Button btn_assignee, btn_category, btn_comments;
        View.OnClickListener assigneeListener, categoryListener, commentListener;

        assigneeListener = new OnAssigneeClickListener();
        categoryListener = new OnCategoryClickListener();
        commentListener = new OnCommentClickListener();

        btn_assignee = (Button) findViewById(R.id.btn_assignee);
        btn_category = (Button) findViewById(R.id.btn_categories);
        btn_comments = (Button) findViewById(R.id.btn_comments);

        btn_assignee.setOnClickListener(assigneeListener);
        btn_category.setOnClickListener(categoryListener);
        btn_comments.setOnClickListener(commentListener);
    }

    private class OnAssigneeClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {

        }
    }

    private class OnCategoryClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {

        }
    }

    private class OnCommentClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {

        }
    }
}
