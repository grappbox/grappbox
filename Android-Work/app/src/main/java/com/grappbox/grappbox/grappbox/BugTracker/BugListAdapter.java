package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Intent;
import android.graphics.Color;
import android.graphics.ColorFilter;
import android.graphics.PorterDuff;
import android.graphics.drawable.Drawable;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.CardView;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.text.TextUtils;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by wieser_m on 18/02/2016.
 */
public class BugListAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private ArrayList<BugEntity> mDataset;
    private BugTrackerFragment context;
    private BugListListener _eventListener;
    private final int VIEW_TYPE_ITEM = 0;
    private final int VIEW_TYPE_LOADING = 1;
    private boolean isLoading = true;

    public interface BugListListener
    {
        void onLoadMore();
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        public CardView mBugView;
        public BugListAdapter adapter;

        public ViewHolder(BugListAdapter adapter, CardView bugView) {
            super(bugView);
            mBugView = bugView;
            this.adapter = adapter;
        }
        public void ConstructView(BugTrackerFragment context, BugEntity entity)
        {
            TextView title = (TextView) mBugView.findViewById(R.id.txt_title);
            TextView creatorAndDate = (TextView) mBugView.findViewById(R.id.txt_openby_date);
            ImageButton btnCloseRecover = (ImageButton) mBugView.findViewById(R.id.btn_close_recover);
            LinearLayout tagLayout = (LinearLayout) mBugView.findViewById(R.id.lay_tags);
            mBugView.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    if (!entity.IsValid())
                        return;
                    Intent intent = new Intent(title.getContext(), EditBugActivity.class);
                    intent.putExtra(BugEntity.EXTRA_GRAPPBOX_BUG_ID, entity.GetId());
                    context.startActivity(intent);
                }
            });

            btnCloseRecover.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    if (!entity.IsValid())
                        return;
                    String saveDeletedAt = entity.GetDeletedAt();
                    if (entity.IsClosed())
                    {

                        ReopenTicketTask task = new ReopenTicketTask(title.getContext(), new OnTaskListener() {
                            @Override
                            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                if (!isErrorOccured)
                                {
                                    context.RefreshOpenList();
                                    return;
                                }
                                entity.SetDeletedAt(saveDeletedAt);
                                adapter.insertData(entity, adapter.getItemCount());
                            }
                        });
                        adapter.removeData(entity);
                        task.execute(entity.GetId());
                    }
                    else
                    {
                        CloseBugTask task = new CloseBugTask(title.getContext(), new OnTaskListener() {
                            @Override
                            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                                if (!isErrorOccured)
                                {
                                    context.RefreshClosedList();
                                    return;
                                }
                                entity.SetDeletedAt(saveDeletedAt);
                                adapter.insertData(entity, adapter.getItemCount());
                            }
                        });
                        adapter.removeData(entity);
                        task.execute(entity.GetId());
                    }
                }
            });

            title.setText(entity._title);
            creatorAndDate.setText(String.format(creatorAndDate.getText().toString(), entity._creatorFullname, entity._createdAt));
            Drawable btnImg;
            int btnColor;
            if (entity.IsClosed())
            {
                btnColor = ContextCompat.getColor(title.getContext(), R.color.colorGrappboxGreen);
                btnImg = ContextCompat.getDrawable(title.getContext(), R.drawable.ic_undo_action);
            }
            else
            {
                btnImg = ContextCompat.getDrawable(title.getContext(), R.drawable.ic_delete_action);
                btnColor = ContextCompat.getColor(title.getContext(), R.color.colorGrappboxRed);
            }
            btnCloseRecover.setColorFilter(btnColor);
            btnCloseRecover.setImageDrawable(btnImg);
            List<TagEntity> tags = entity.GetTags();
            tagLayout.removeAllViews();
            for (TagEntity tag : tags)
            {
                RelativeLayout newTagLayout = new RelativeLayout(title.getContext());
                ImageView img = new ImageView(title.getContext());
                img.setImageDrawable(ContextCompat.getDrawable(title.getContext(), R.drawable.draw_tag));
                img.setColorFilter(Color.parseColor(tag.GetColor()));
                img.setBaselineAlignBottom(true);
                TextView tagName = new TextView(title.getContext());
                tagName.setEllipsize(TextUtils.TruncateAt.END);
                tagName.setText(tag.GetName());
                tagName.setTextAlignment(View.TEXT_ALIGNMENT_CENTER);
                tagName.setTextColor(ContextCompat.getColor(title.getContext(), R.color.White));
                RelativeLayout.LayoutParams params = new RelativeLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
                params.setMargins(0,0,0,0);
                newTagLayout.addView(img, params);
                tagName.setGravity(Gravity.CENTER);
                newTagLayout.addView(tagName, params);
                LinearLayout.LayoutParams tagLayoutParam = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
                tagLayoutParam.setMargins(0,0,0,0);
                tagLayout.addView(newTagLayout, tagLayoutParam);
            }
        }
    }

    static class LoadingViewHolder extends RecyclerView.ViewHolder {
        public ProgressBar progressBar;

        public LoadingViewHolder(View itemView) {
            super(itemView);
            progressBar = (ProgressBar) itemView.findViewById(R.id.progress);
            Drawable dpb = progressBar.getIndeterminateDrawable();
            dpb.setColorFilter(ContextCompat.getColor(itemView.getContext(), R.color.colorGrappboxRed), PorterDuff.Mode.OVERLAY);
            progressBar.setIndeterminateDrawable(dpb);
        }
    }

    // Provide a suitable constructor (depends on the kind of dataset)
    public BugListAdapter(BugTrackerFragment context, ArrayList<BugEntity> dataset, RecyclerView recyclerView) {
        mDataset = dataset;
        this.context = context;
        LinearLayoutManager layoutManager = (LinearLayoutManager) recyclerView.getLayoutManager();

        recyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                int totalItemCount = layoutManager.getItemCount();
                int lastVisibleItem = layoutManager.findLastVisibleItemPosition();
                Log.e("BugListAdapter", "totalItemCount = " + String.valueOf(totalItemCount));
                Log.e("BugListAdapter", "lastVisibleItem = " + String.valueOf(lastVisibleItem + 5));
                if (!isLoading && totalItemCount <= (lastVisibleItem + 5)) {
                    if (_eventListener != null) {
                        _eventListener.onLoadMore();
                    }
                    isLoading = true;
                }
            }
        });
    }

    public void setListener(BugListListener listener)
    {
        _eventListener = listener;
    }

    public void setLoaded()
    {
        isLoading = false;
    }

    public void insertData(BugEntity data, int position)
    {
        if (position == -1)
            position = getItemCount();
        mDataset.add(position, data);
        notifyItemInserted(position);
    }

    public void removeData(BugEntity data)
    {
        int index = mDataset.indexOf(data);
        mDataset.remove(data);
        notifyItemRemoved(index);
    }

    public void clearData()
    {
        mDataset = new ArrayList<>();
        notifyDataSetChanged();
    }

    // Create new views (invoked by the layout manager)
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent,
                                                   int viewType) {
        // create a new view
        if (viewType == VIEW_TYPE_ITEM)
        {
            View v = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.card_bugtracker_list, parent, false);
            ViewHolder vh = new ViewHolder(this, (CardView) v);
            return vh;
        }
        View view = LayoutInflater.from(context.getContext()).inflate(R.layout.progress_recyclerviews, parent, false);
        return new LoadingViewHolder(view);
    }
    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        // - get element from your dataset at this position
        // - replace the contents of the view with that element
        if (holder instanceof BugListAdapter.ViewHolder)
            ((BugListAdapter.ViewHolder)holder).ConstructView(context, mDataset.get(position));
        else if (holder instanceof LoadingViewHolder) {
            LoadingViewHolder loadingViewHolder = (LoadingViewHolder) holder;
            loadingViewHolder.progressBar.setIndeterminate(true);
        }
    }

    // Return the size of your dataset (invoked by the layout manager)
    @Override
    public int getItemCount() {
        return mDataset.size();
    }

    @Override
    public int getItemViewType(int position) {
        return mDataset.get(position) == null ? VIEW_TYPE_LOADING : VIEW_TYPE_ITEM;
    }
}