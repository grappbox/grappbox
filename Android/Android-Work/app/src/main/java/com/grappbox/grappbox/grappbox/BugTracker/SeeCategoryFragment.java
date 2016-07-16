package com.grappbox.grappbox.grappbox.BugTracker;


import android.app.Dialog;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

/**
 * A simple {@link Fragment} subclass.
 */
public class SeeCategoryFragment extends LoadingFragment {

    private BugEntity _bug;
    public SeeCategoryFragment() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setRetainInstance(true);

    }

    public void InitCheckboxes()
    {
        View v = getView();
        int bugtrackerAccess = SessionAdapter.getInstance().getAuthorizations().getAuthorization("bugtracker").ordinal();
        if (v == null)
            return;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.category_container);

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);

            for (TagEntity user : _bug.GetTags())
            {
                if (Objects.equals(user.GetId(), current.GetStoredId()))
                {
                    current.setChecked(true);
                    break;
                }
            }
            current.setEnabled(bugtrackerAccess > 1);
        }
    }

    public List<Pair<String, Boolean>> DiffIds()
    {
        List<Pair<String, Boolean>> idView = new ArrayList<>();
        View v = getView();
        assert v != null;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.category_container);

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);
            boolean isSet = false;
            Pair<String, Boolean> possibleAdd = new Pair<>("", true);
            for (TagEntity user : _bug.GetTags())
            {
                if (Objects.equals(user.GetId(), current.GetStoredId()))
                {
                    isSet = true;
                    if (!current.isChecked())
                        idView.add(new Pair<>(current.GetStoredId(), false));
                    break;
                }
            }
            if (!isSet && current.isChecked()) {
                idView.add(new Pair<>(current.GetStoredId(), true));
            }
        }
        return idView;
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v;
        if (SessionAdapter.getInstance().getAuthorizations().getAuthorization("bugtracker").ordinal() <= 1)
            v = inflater.inflate(R.layout.fragment_see_category_read_only, container, false);
        else
            v = inflater.inflate(R.layout.fragment_see_category, container, false);
        startLoading(v, R.id.loader, R.id.scroller);
        if (!(getActivity() instanceof EditBugActivity)){
            getActivity().onBackPressed();
            endLoading();
            return v;
        }

        Button btn_save = (Button) v.findViewById(R.id.btn_save);
        FloatingActionButton btn_add_tag = (FloatingActionButton) v.findViewById(R.id.add_tag);
        LinearLayout _adapter = (LinearLayout) v.findViewById(R.id.category_container);
        btn_add_tag.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                builder.setView(R.layout.dialog_add_tag);
                builder.setTitle("Create a new tag");
                builder.setNegativeButton(getString(R.string.negative_response), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.dismiss();
                    }
                });
                builder.setPositiveButton(getString(R.string.positive_response), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        Dialog rdial = (Dialog) dialog;
                        EditText tag_title = (EditText) rdial.findViewById(R.id.txt_tagName);
                        CreateTagTask task = new CreateTagTask(new CreateTagTask.CreateTagListener() {
                            @Override
                            public void onTaskEnd(boolean success, String id) {
                                if (!success || id == null)
                                    return;
                                View lay = LayoutInflater.from(getActivity()).inflate(R.layout.li_checkable_item, null);
                                BugIdCheckbox cb = (BugIdCheckbox) lay.findViewById(R.id.cb_assigned);
                                ImageButton btnDelete = (ImageButton) lay.findViewById(R.id.btn_delete_tag);
                                btnDelete.setVisibility(View.VISIBLE);
                                btnDelete.setOnClickListener(new View.OnClickListener() {
                                    @Override
                                    public void onClick(View v) {
                                        int position = _adapter.indexOfChild(lay);
                                        _adapter.removeView(lay);
                                        DeleteTagTask deleteTask = new DeleteTagTask(getActivity(), new DeleteTagTask.DeleteTagListener() {
                                            @Override
                                            public void onDeletionEnd(boolean success) {
                                                if (!success)
                                                    _adapter.addView(lay, position);
                                            }
                                        });
                                        deleteTask.execute(id);
                                    }
                                });
                                cb.setText(tag_title.getText().toString());
                                cb.SetId(id);
                                _adapter.addView(lay);
                            }
                        }, getActivity());
                        task.execute(tag_title.getText().toString());
                    }
                });
                builder.show();
            }
        });

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
                    GetBugTagTask btask = new GetBugTagTask(getActivity(), (LinearLayout) v.findViewById(R.id.category_container), new OnTaskListener() {
                        @Override
                        public void OnTaskEnd(boolean isErrorOccured, String... params) {
                            InitCheckboxes();
                            endLoading();
                        }
                    });
                    btask.execute();
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        if (getActivity() instanceof EditBugActivity)
            task.execute(((EditBugActivity) getActivity()).GetModelId());
        btn_save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                List<Pair<String, Boolean>> rmAndAdd = DiffIds();

                AssignBatchTagTask task = new AssignBatchTagTask(getActivity(), new OnTaskListener() {
                    @Override
                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                        getActivity().onBackPressed();
                    }
                }, rmAndAdd);
                task.execute(((EditBugActivity)getActivity()).GetModelId());
            }
        });
        return v;
    }

}
