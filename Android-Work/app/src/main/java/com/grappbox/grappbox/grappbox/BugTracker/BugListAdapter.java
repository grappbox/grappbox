package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.content.Intent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AbsListView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class BugListAdapter extends ArrayAdapter<BugEntity> implements AbsListView.OnScrollListener {
    private BugTrackerFragment _parent;

    public BugListAdapter(Context context, int resource) {
        super(context, resource);
    }

    public BugListAdapter(Context context, int resource, BugEntity[] objects) {
        super(context, resource, objects);
    }

    public BugListAdapter SetParentFragment(BugTrackerFragment parent)
    {
        _parent = parent;
        return this;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = convertView;
        BugListAdapter me = this;
        if (v == null) {
            LayoutInflater vi;
            vi = LayoutInflater.from(getContext());
            v = vi.inflate(R.layout.lvitem_bug, null);
        }

        BugEntity bug = getItem(position);

        if (bug != null) {
            TextView title = (TextView) v.findViewById(R.id.txt_bugtitle);
            Button btnClose = (Button) v.findViewById(R.id.btn_close);

            if (title != null) {
                title.setText(bug.GetTitle());
                title.setClickable(true);
                title.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (!bug.IsValid())
                            return;
                        Intent intent = new Intent(getContext(), EditBugActivity.class);
                        intent.putExtra(EditBugActivity.EXTRA_GRAPPBOX_BUG_ID, bug.GetId());
                        _parent.getActivity().startActivity(intent);

                    }
                });
            }
            if (btnClose != null)
            {
                if (bug.IsClosed())
                {
                    String saveDeletedAt = bug.GetDeletedAt();
                    btnClose.setText(R.string.bug_reopen);
                    btnClose.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            ReopenTicketTask task = new ReopenTicketTask(getContext(), new OnTaskListener() {
                                @Override
                                public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                    if (!isErrorOccured || params.length < 1)
                                    {
                                        _parent.RefreshOpenList();
                                        return;
                                    }
                                    String id = params[0];
                                    bug.SetDeletedAt(saveDeletedAt);
                                    me.insert(bug, position);
                                }
                            });
                            me.remove(bug);
                            task.execute(bug.GetId());
                        }
                    });
                }
                else
                {
                    String saveDeletedAt = bug.GetDeletedAt();
                    btnClose.setText(R.string.bug_close);
                    btnClose.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            CloseBugTask task = new CloseBugTask(getContext(), new OnTaskListener() {
                                @Override
                                public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                    if (!isErrorOccured || params.length < 1)
                                    {
                                        _parent.RefreshClosedList();
                                        return;
                                    }

                                    String id = params[0];
                                    bug.SetDeletedAt(saveDeletedAt);
                                    me.insert(bug, position);
                                }
                            });
                            me.remove(bug);
                            task.execute(bug.GetId());

                        }
                    });
                }
            }
        }

        return v;
    }

    @Override
    public void onScrollStateChanged(AbsListView view, int scrollState) {

    }

    @Override
    public void onScroll(AbsListView view, int firstVisibleItem, int visibleItemCount, int totalItemCount) {
        if (totalItemCount < 20)
            return;
        if (firstVisibleItem % 15 == 0)
        {
            GetLastTicketsTask task = new GetLastTicketsTask(getContext(), this, false, totalItemCount, 20);
            task.execute();
        }
    }
}
