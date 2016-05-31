package com.grappbox.grappbox.grappbox.BugTracker;


import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * A simple {@link Fragment} subclass.
 */
public class SeeCommentFragment extends Fragment {
    private BugCommentAdapter _adapter;

    public SeeCommentFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_see_comment, container, false);
        ListView commentList = (ListView) v.findViewById(R.id.lv_comment);
        Button btnNew = (Button) v.findViewById(R.id.btn_comment);

        _adapter = new BugCommentAdapter(getContext(), R.layout.li_bug_comment);
        commentList.setAdapter(_adapter);

        GetBugCommentTask task = new GetBugCommentTask(getActivity(), new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                if (isErrorOccured || params.length < 1)
                    return;
                try {
                    JSONObject data = new JSONObject(params[0]);
                    JSONArray array = data.getJSONArray("array");

                    for (int i = 0; i < array.length(); ++i)
                        _adapter.add(new BugCommentEntity(array.getJSONObject(i)));
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        task.execute(((EditBugActivity)getActivity()).GetModel().GetId());

        commentList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                BugCommentEntity entity = (BugCommentEntity) parent.getItemAtPosition(position);

                if (!entity.getAuthorId().equals(SessionAdapter.getInstance().getUserID()))
                    return;
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                View v = View.inflate(getActivity(), R.layout.dialog_comment, null);

                builder.setTitle("Edit " + entity.getTitle());
                builder.setNegativeButton(null, null);
                builder.setPositiveButton(null, null);
                builder.setItems(R.array.bugtracker_comment_choice, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        String choice = getResources().getStringArray(R.array.bugtracker_comment_choice_universal)[which];
                        switch (choice) {
                            case "action_edit":
                                OnEditComment(entity, position);
                                break;
                            case "action_delete":
                                OnDeleteComment(entity);
                                break;
                        }
                        dialog.dismiss();
                    }
                });
                builder.show();
            }
        });

        btnNew.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                View nv = View.inflate(getActivity(), R.layout.dialog_comment, null);

                builder.setTitle(R.string.str_new_comment_title);
                builder.setView(nv);
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        EditText title = (EditText) nv.findViewById(R.id.et_title);
                        EditText desc = (EditText) nv.findViewById(R.id.et_description);
                        PostCommentTask task = new PostCommentTask(getActivity(), new OnTaskListener() {
                            @Override
                            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                if (isErrorOccured || params.length < 1 || params[0] == null)
                                    return;
                                try {
                                    JSONObject data = new JSONObject(params[0]);
                                    Log.e("API", params[0]);
                                    _adapter.add(new BugCommentEntity(data));
                                } catch (JSONException e) {
                                    e.printStackTrace();
                                }
                            }
                        });
                        task.execute(title.getText().toString(), desc.getText().toString(), ((EditBugActivity) getActivity()).GetModel().GetId());
                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.show();
            }
        });
        return v;
    }


    public void OnEditComment(BugCommentEntity entity, int adapterPosition)
    {
        View v = View.inflate(getActivity(), R.layout.dialog_comment, null);
        EditText title = (EditText) v.findViewById(R.id.et_title);
        EditText desc = (EditText) v.findViewById(R.id.et_description);
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

        title.setText(entity.getTitle());
        desc.setText(entity.getContent());
        builder.setTitle("Edit " + entity.getTitle());
        builder.setView(v);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                EditCommentTask ectask = new EditCommentTask(getActivity(), new OnTaskListener() {
                    @Override
                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                        if (isErrorOccured || params.length < 1 || params[0] == null)
                            return;
                        try {
                            JSONObject data = new JSONObject(params[0]);
                            _adapter.remove(entity);
                            entity.reimport(data);
                            _adapter.insert(new BugCommentEntity(data), adapterPosition);
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                });
                ectask.execute(entity.getId(), title.getText().toString(), desc.getText().toString());
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        builder.show();
    }

    public void OnDeleteComment(BugCommentEntity entity)
    {
        View v = View.inflate(getActivity(), R.layout.dialog_comment, null);
        EditText title = (EditText) v.findViewById(R.id.et_title);
        EditText desc = (EditText) v.findViewById(R.id.et_description);
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

        title.setText(entity.getTitle());
        desc.setText(entity.getContent());
        builder.setTitle("Delete " + entity.getTitle());
        builder.setMessage("Are you sure you want to delete " + entity.getTitle() + "?");
        builder.setPositiveButton(getActivity().getString(R.string.yes_answer), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                CloseBugTask dtask = new CloseBugTask(getActivity(), new OnTaskListener() {
                    @Override
                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                        if (isErrorOccured)
                            return;
                        _adapter.remove(entity);
                    }
                });
                dtask.execute(entity.getId());
            }
        });
        builder.setNegativeButton(getActivity().getString(R.string.no_answer), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        builder.show();
    }
}
