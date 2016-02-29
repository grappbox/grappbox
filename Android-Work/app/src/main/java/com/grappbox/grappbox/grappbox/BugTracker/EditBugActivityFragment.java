package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * A placeholder fragment containing a simple view.
 */
public class EditBugActivityFragment extends Fragment {

    private BugEntity _bug;

    public EditBugActivityFragment() {

    }

    public void SetBugEntity(BugEntity bugEntity)
    {
        TextView title, description;
        View v;

        assert getView() != null;
        v = getView();
        title = (TextView) getView().findViewById(R.id.et_title);
        description = (TextView) getView().findViewById(R.id.et_description);

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
                    Button btn_close;
                    btn_close = (Button) v.findViewById(R.id.btn_save);
                    if (_bug.IsClosed()) {
                        btn_close.setText("Re Open Ticket");
                        btn_close.setBackgroundColor(ContextCompat.getColor(getActivity(), R.color.colorGrappboxGreen));
                    }
                    btn_close.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            if (_bug.IsClosed()) {
                                CloseBugTask task = new CloseBugTask(getActivity(), new OnTaskListener() {
                                    @Override
                                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                        if (!isErrorOccured)
                                            getActivity().onBackPressed();
                                    }
                                });
                                task.execute(_bug.GetId());
                            } else {
                                ReopenTicketTask task = new ReopenTicketTask(getActivity(), new OnTaskListener() {
                                    @Override
                                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                        if (!isErrorOccured)
                                            getActivity();
                                    }
                                });
                                task.execute(_bug.GetId());
                            }
                        }
                    });
                }
            });
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v =  inflater.inflate(R.layout.fragment_edit_bug, container, false);
        Button btn_assignee, btn_category, btn_comments;
        View.OnClickListener assigneeListener, categoryListener, commentListener;


        _bug = ((EditBugActivity) getActivity()).GetModel();
        assigneeListener = new OnAssigneeClickListener();
        categoryListener = new OnCategoryClickListener();
        commentListener = new OnCommentClickListener();

        btn_assignee = (Button) v.findViewById(R.id.btn_assignee);
        btn_category = (Button) v.findViewById(R.id.btn_categories);
        btn_comments = (Button) v.findViewById(R.id.btn_comments);

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
