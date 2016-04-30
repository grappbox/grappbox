package com.grappbox.grappbox.grappbox.Project;

import android.app.Activity;
import android.app.AlertDialog;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import com.grappbox.grappbox.grappbox.Model.UserProjectTask;
import com.grappbox.grappbox.grappbox.R;

public class CreateProjectActivity extends AppCompatActivity {

    private Activity _currentActivity = this;
    EditText _projectTitle;
    EditText _projectDescription;
    EditText _projectPhone;
    EditText _projectMail;
    EditText _projectCompany;
    EditText _projectFacebook;
    EditText _projectTwitter;
    EditText _projectSafePassword;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_project);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        setTitle(R.string.str_project_settings_title);
        android.app.ActionBar actionBar = getActionBar();
        if (actionBar != null)
            actionBar.setDisplayHomeAsUpEnabled(true);
        _projectTitle = (EditText) findViewById(R.id.create_project_title);
        _projectDescription = (EditText) findViewById(R.id.create_project_description);
        _projectPhone = (EditText)findViewById(R.id.create_project_phone);
        _projectMail = (EditText)findViewById(R.id.create_project_mail);
        _projectCompany = (EditText)findViewById(R.id.create_project_company);
        _projectFacebook = (EditText)findViewById(R.id.create_project_facebook);
        _projectTwitter = (EditText)findViewById(R.id.create_project_twitter);
        _projectSafePassword = (EditText)findViewById(R.id.create_project_safe_passeword);
    }

    public void sendCreateProject(View view)
    {
        String title = _projectTitle.getText().toString();
        String desc = _projectDescription.getText().toString();
        String phone = _projectPhone.getText().toString();
        String mail = _projectMail.getText().toString();
        String company = _projectCompany.getText().toString();
        String facebook = _projectFacebook.getText().toString();
        String twitter = _projectTwitter.getText().toString();
        String safePassword = _projectSafePassword.getText().toString();
        if (title.matches("") || safePassword.matches("")){
            AlertDialog.Builder builder = new AlertDialog.Builder(_currentActivity);
            builder.setTitle(R.string.project_create_alert_error);
            builder.setMessage(R.string.project_create_alert_error_message);
            builder.show();
        }
        APIRequestCreateProject api = new APIRequestCreateProject(this);
        api.execute(title, safePassword, desc, phone, company, facebook, twitter, mail);
    }
}
