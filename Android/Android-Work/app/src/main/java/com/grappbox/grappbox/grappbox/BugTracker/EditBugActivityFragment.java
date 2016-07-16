package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.ActionBar;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toolbar;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * A placeholder fragment containing a simple view.
 */
public class EditBugActivityFragment extends LoadingFragment {

    private BugEntity _bug;

    public EditBugActivityFragment() {

    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setRetainInstance(true);
    }

    public void SetBugEntity(BugEntity bugEntity)
    {
        TextView title, description;
        View v;


        v = getView();
        if (v == null)
            return;
        title = (TextView) v.findViewById(R.id.et_title);
        description = (TextView) v.findViewById(R.id.et_description);

        _bug = bugEntity;
        title.setText(_bug.GetTitle());
        description.setText(_bug.GetDescription());
        TextView status = (TextView) v.findViewById(R.id.txt_status);
        TextView owner = (TextView) v.findViewById(R.id.txt_owner);
        Button btn_close = (Button) v.findViewById(R.id.btn_close);
        String strStatus = "Status : " + (_bug.IsClosed() ? "Closed" : "Open");
        String strOwner = "by " + _bug.GetCreatorFullname();

        status.setText(strStatus);
        owner.setText(strOwner);
        if (_bug.GetCreatorId().equals(SessionAdapter.getInstance().getUserID()))
        {
            Button btn_save = (Button) v.findViewById(R.id.btn_save);

            title.setEnabled(true);
            description.setEnabled(true);
            btn_save.setVisibility(View.VISIBLE);

            btn_save.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    EditTicketTask task = new EditTicketTask(getActivity(), new OnTaskListener() {
                        @Override
                        public void OnTaskEnd(boolean isErrorOccured, String... params) {
                            if (!isErrorOccured && params.length > 0) {
                                try {
                                    _bug.reimport(new JSONObject(params[0]));
                                    AlertDialog.Builder builder = new AlertDialog.Builder(getActivity(), R.style.AppTheme);
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

            if (_bug.IsClosed()) {
                btn_close.setText("Re Open Ticket");
                btn_close.setBackgroundColor(ContextCompat.getColor(getActivity(), R.color.colorGrappboxGreen));
            }
            btn_close.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    if (_bug.IsClosed()) {
                        ReopenTicketTask task = new ReopenTicketTask(getActivity(), new OnTaskListener() {
                            @Override
                            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                if (!isErrorOccured)
                                    getActivity().onBackPressed();
                            }
                        });
                        task.execute(_bug.GetId());
                    } else {
                        CloseBugTask task = new CloseBugTask(getActivity(), new OnTaskListener() {
                            @Override
                            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                if (!isErrorOccured)
                                    getActivity().onBackPressed();
                            }
                        });
                        task.execute(_bug.GetId());
                    }
                }
            });
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = null;
            if (SessionAdapter.getInstance().getAuthorizations().getAuthorization("bugtracker").ordinal() <= 1)
                v = inflater.inflate(R.layout.fragment_edit_bug_read_only, container, false);
            else
                v = inflater.inflate(R.layout.fragment_edit_bug, container, false);
        Button btn_assignee, btn_category, btn_comments, btn_save, btn_close;
        View.OnClickListener assigneeListener, categoryListener, commentListener;
        startLoading(v, R.id.loader, R.id.lay_assignees, R.id.lay_categories, R.id.lay_comments, R.id.btn_save, R.id.btn_close, R.id.et_description, R.id.et_title, R.id.lay_status);
        assigneeListener = new OnAssigneeClickListener();
        categoryListener = new OnCategoryClickListener();
        commentListener = new OnCommentClickListener();

        btn_assignee = (Button) v.findViewById(R.id.btn_assignee);
        btn_category = (Button) v.findViewById(R.id.btn_categories);
        btn_comments = (Button) v.findViewById(R.id.btn_comments);
        btn_save = (Button) v.findViewById(R.id.btn_save);
        btn_close = (Button) v.findViewById(R.id.btn_close);

        GetTicketTask task = new GetTicketTask(this.getActivity(), new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {

                if (isErrorOccured || params.length < 1) {
                    getActivity().onBackPressed();
                    return;
                }
                try {
                    JSONObject data = new JSONObject(params[0]);
                    _bug = new BugEntity(data);
                    if (getActivity() instanceof EditBugActivity)
                    {
                        ActionBar toolbar = ((EditBugActivity) getActivity()).getSupportActionBar();
                        toolbar.setTitle("Edit " + _bug.GetTitle());
                    }
                    SetBugEntity(_bug);
                    endLoading();
                    if (SessionAdapter.getInstance().getAuthorizations().getAuthorization("bugtracker").ordinal() <= 1)
                    {
                        btn_close.setVisibility(View.GONE);
                        btn_save.setVisibility(View.GONE);
                    }
                    else if (_bug._creatorId != SessionAdapter.getInstance().getUserID())
                        btn_save.setVisibility(View.GONE);
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        if (getActivity() instanceof EditBugActivity)
            task.execute(((EditBugActivity) getActivity()).GetModelId());
        btn_assignee.setOnClickListener(assigneeListener);
        btn_category.setOnClickListener(categoryListener);
        btn_comments.setOnClickListener(commentListener);

        return v;
    }



    private class OnAssigneeClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {
            getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new SeeAssigneeFragment()).addToBackStack(null).commit();
        }
    }

    private class OnCategoryClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {
            getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new SeeCategoryFragment()).addToBackStack(null).commit();
        }
    }

    private class OnCommentClickListener implements View.OnClickListener
    {

        @Override
        public void onClick(View v) {
            getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new SeeCommentFragment()).addToBackStack(null).commit();
        }
    }
}
