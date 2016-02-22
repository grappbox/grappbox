package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
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
    public BugListAdapter(Context context, int resource) {
        super(context, resource);
    }

    public BugListAdapter(Context context, int resource, BugEntity[] objects) {
        super(context, resource, objects);
    }



    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = convertView;

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
            }
            if (btnClose != null)
            {
                if (bug.IsClosed())
                {
                    btnClose.setText(R.string.bug_reopen);
                    btnClose.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            //TODO : Reopen
                        }
                    });
                }
                else
                {
                    btnClose.setText(R.string.bug_close);
                    btnClose.setOnClickListener(new View.OnClickListener() {
                        @Override
                        public void onClick(View v) {
                            //TODO : Close bug task
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
