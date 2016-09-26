package com.grappbox.grappbox.project_fragments;


import android.Manifest;
import android.accounts.AccountManager;
import android.app.Activity;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.database.Cursor;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.design.widget.BottomSheetDialog;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CloudListAdapter;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.receiver.ErrorReceiver;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.apache.commons.io.FileUtils;

/**
 * A simple {@link Fragment} subclass.
 */
public class CloudFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, CloudListAdapter.CloudAdapterListener, AdapterView.OnItemClickListener {
    private static final int LOADER_LOAD_FILELIST = 0;
    public static final String BUNDLE_KEY_CLOUD_PATH = "com.grappbox.grappbox.project_fragments.CloudFragment.BUNDLE_KEY_CLOUD_PATH";
    private static final String LOG_TAG = CloudFragment.class.getSimpleName();
    public static final String CLOUD_SHARED_PREF = "com.grappbox.grappbox.shared_pref.cloud";
    public static final String CLOUD_PREF_SAFE_BASE_KEY = "Safe-";
    private static final String BUNDLE_KEY_CLOUD_PASS = "cloud_safe_pass";

    private static final int REQUEST_IMPORT_FILE = 1000;
    private static final int REQUEST_IMPORT_FILE_SECURED = 1001;

    private static final int PERMISSION_REQUEST_CLOUD_DOWNLOAD = 9000;

    private String mPath = "/";
    private ListView mCloudEntries;
    private CloudListAdapter mAdapter;
    private FloatingActionButton mAddAction;
    private boolean mNeverSync = true;
    private SwipeRefreshLayout mRefreshLayout;
    private RefreshReceiver mRefreshReceiver;
    private ErrorReceiver mErrorReceiver;

    public CloudFragment() {
        // Required empty public constructor
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(LOADER_LOAD_FILELIST, null, this);
    }

    public void syncCurrentPath(){

        Intent refreshList = new Intent(getActivity(), GrappboxJustInTimeService.class);
        refreshList.setAction(GrappboxJustInTimeService.ACTION_SYNC_CLOUD_PATH);
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        refreshList.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);

        if (mPath.contains("/Safe")){
            refreshList.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
        }
        getActivity().startService(refreshList);
        mNeverSync = false;
    }

    private String getSafePassword(){
        SharedPreferences prefs = getActivity().getSharedPreferences(CLOUD_SHARED_PREF, Context.MODE_PRIVATE);
        long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        if (projectId == -1)
            return null;
        return prefs.getString(CLOUD_PREF_SAFE_BASE_KEY + String.valueOf(projectId), null);
    }

    private void createDirectory(){
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        View dialogView = LayoutInflater.from(getActivity()).inflate(R.layout.dialog_cloud_create_dir, null);
        final String safePassword = getSafePassword();
        AccountManager am = AccountManager.get(getActivity());
        final String token = am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);

        if (!mPath.contains("/Safe") || safePassword != null){
            dialogView.findViewById(R.id.til_safe).setVisibility(View.GONE);
        }

        builder.setTitle(R.string.new_directory);
        builder.setView(dialogView);

        builder.setPositiveButton(getActivity().getString(R.string.positive_response), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                AlertDialog dialog = (AlertDialog) dialogInterface;
                EditText dirname = ((EditText)dialog.findViewById(R.id.input_dirname));

                if (dirname == null){
                    dialog.cancel();
                    return;
                }
                Intent createDir = new Intent(getActivity(), GrappboxJustInTimeService.class);
                createDir.setAction(GrappboxJustInTimeService.ACTION_CLOUD_ADD_DIRECTORY);
                createDir.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                createDir.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                createDir.putExtra(GrappboxJustInTimeService.EXTRA_DIRECTORY_NAME, dirname.getText().toString());
                long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
                createDir.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
                createDir.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mErrorReceiver);

                if (mPath.contains("/Safe")){
                    String pass;
                    if (safePassword != null){
                        pass = safePassword;
                    } else {
                        EditText editSafe = ((EditText)dialog.findViewById(R.id.input_safe));
                        if (editSafe == null)
                            pass = "";
                        else{
                            pass = editSafe.getText().toString();
                            if (projectId != -1)
                                getActivity().getSharedPreferences(CLOUD_SHARED_PREF, Context.MODE_PRIVATE).edit().putString(CLOUD_PREF_SAFE_BASE_KEY.concat(String.valueOf(projectId)), pass).apply();
                        }

                    }
                    createDir.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, pass);
                }
                getActivity().startService(createDir);
            }
        });
        builder.setNegativeButton(getActivity().getString(R.string.negative_response), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                dialogInterface.dismiss();
            }
        });
        builder.show();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, final Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (resultCode == Activity.RESULT_CANCELED)
            return;
        AccountManager am = AccountManager.get(getActivity());
        final String token = am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
        if (requestCode == REQUEST_IMPORT_FILE){
            Uri contentURI = data.getData();

            Intent streamLaunch = new Intent(getActivity(), GrappboxJustInTimeService.class);
            streamLaunch.setData(contentURI);
            streamLaunch.setAction(GrappboxJustInTimeService.ACTION_CLOUD_IMPORT_FILE);
            streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
            streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
            streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
            if (mPath.contains("/Safe")){
                streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
            }
            getActivity().startService(streamLaunch);
        } else if (requestCode == REQUEST_IMPORT_FILE_SECURED) {
            AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

            builder.setView(R.layout.dialog_secured_file);
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    AlertDialog dialog = (AlertDialog) dialogInterface;

                    Intent streamLaunch = new Intent(getActivity(), GrappboxJustInTimeService.class);
                    streamLaunch.setData(data.getData());
                    streamLaunch.setAction(GrappboxJustInTimeService.ACTION_CLOUD_IMPORT_FILE);
                    streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                    streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                    streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                    streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_FILE_PASSWORD, ((EditText) dialog.findViewById(R.id.input_password)).getText().toString());
                    if (mPath.contains("/Safe")){
                        streamLaunch.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
                    }
                    getActivity().startService(streamLaunch);
                }
            });
            builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                        dialogInterface.dismiss();
                }
            });
            builder.show();
        }
    }

    private void importFile(boolean isSecured){
        Intent launchChooseFile = new Intent(Intent.ACTION_GET_CONTENT);

        launchChooseFile.addCategory(Intent.CATEGORY_OPENABLE);
        launchChooseFile.setType("*/*");
        if (launchChooseFile.resolveActivity(getActivity().getPackageManager()) == null){
            Snackbar.make(getView(), getString(R.string.error_no_app, getString(R.string.file_explorer_app)), Snackbar.LENGTH_LONG).show();
        } else {
            startActivityForResult(launchChooseFile, isSecured ? REQUEST_IMPORT_FILE_SECURED : REQUEST_IMPORT_FILE);
        }

    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        switch (requestCode){
            case PERMISSION_REQUEST_CLOUD_DOWNLOAD:
                if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED){
                    Snackbar.make(getActivity().findViewById(R.id.fragment_container), R.string.congrats_download_granted, Snackbar.LENGTH_LONG).show();
                } else {
                    Snackbar.make(getActivity().findViewById(R.id.fragment_container), R.string.permission_grant_error, Snackbar.LENGTH_LONG).show();
                }
                break;
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment

        mPath = ((getArguments() == null || getArguments().getString(BUNDLE_KEY_CLOUD_PATH) == null) ? "/" : getArguments().getString(BUNDLE_KEY_CLOUD_PATH));
        assert mPath != null;
        if (mPath.contains("/Safe") && getSafePassword() == null){
            getActivity().getSupportFragmentManager().popBackStack();
        }
        if (savedInstanceState != null)
            getLoaderManager().initLoader(LOADER_LOAD_FILELIST, null, this);
        View v =  inflater.inflate(R.layout.fragment_cloud, container, false);
        View listHeaderView = inflater.inflate(R.layout.cloud_list_header, container, false);

        mCloudEntries = (ListView) v.findViewById(R.id.cloud_entries);
        mRefreshLayout = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefreshLayout, getActivity());
        mErrorReceiver = new ErrorReceiver(new Handler(), getActivity());
        mRefreshLayout.setOnRefreshListener(new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                mRefreshLayout.setRefreshing(true);
                syncCurrentPath();
            }
        });
        mAdapter = new CloudListAdapter(getActivity(), null, 0);

        mAdapter.setListener(this);
        mCloudEntries.setAdapter(mAdapter);
        mCloudEntries.setOnItemClickListener(this);
        String breadcrumb = ("Root" + mPath.replace("/", " > "));
        breadcrumb = breadcrumb.substring(0, breadcrumb.length() - 3);
        ((TextView)listHeaderView.findViewById(R.id.path)).setText(breadcrumb);
        mCloudEntries.addHeaderView(listHeaderView);

        mAddAction = (FloatingActionButton) v.findViewById(R.id.fab);
        mAddAction.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                View bottomSheetView = getActivity().getLayoutInflater().inflate(R.layout.cloud_list_bottom_sheet, null);
                final BottomSheetDialog dialog = new BottomSheetDialog(getActivity());

                bottomSheetView.findViewById(R.id.directory).setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        createDirectory();
                        dialog.dismiss();
                    }
                });

                bottomSheetView.findViewById(R.id.importfile).setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        importFile(false);
                        dialog.dismiss();
                    }
                });

                bottomSheetView.findViewById(R.id.importsecure).setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        importFile(true);
                        dialog.dismiss();
                    }
                });

                dialog.setContentView(bottomSheetView);
                dialog.show();
            }
        });
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_LOAD_FILELIST){
            Log.d(LOG_TAG, "Loader created");
            String sortOrder = CloudEntry.COLUMN_TYPE + " DESC";
            String selection = CloudEntry.TABLE_NAME + "." + CloudEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + CloudEntry.COLUMN_PATH + "=?";
            String[] selectArgs = new String[]{
                String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)),
                mPath
            };
            return new CursorLoader(getActivity(), CloudEntry.buildWithProjectJoin(), CloudListAdapter.cloudProjection, selection, selectArgs, sortOrder);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader loader, Cursor data) {
        if (loader.getId() == LOADER_LOAD_FILELIST){
            Log.d(LOG_TAG, "loading ended");
            if (data.getCount() == 0 && mNeverSync){
                syncCurrentPath();
                return;
            }
            mAdapter.swapCursor(data);
        }

    }

    @Override
    public void onLoaderReset(Loader loader) {
        if (loader.getId() == LOADER_LOAD_FILELIST)
            mAdapter.swapCursor(null);
    }

    @Override
    public void onMoreClicked(int position) {
        Cursor item = (Cursor) mAdapter.getItem(position);
        View moreDialogView = LayoutInflater.from(getActivity()).inflate(R.layout.cloud_list_more_bottom_sheet, null);
        final BottomSheetDialog moreDialog = new BottomSheetDialog(getActivity());
        final String filename = item.getString(CloudListAdapter.COLUMN_FILENAME);
        final boolean isSecured = item.getInt(CloudListAdapter.COLUMN_IS_SECURED) > 0;
        TextView title, filesize, filetype, lastModified;
        LinearLayout download, delete;

        title = ((TextView)moreDialogView.findViewById(R.id.title));
        filesize = ((TextView)moreDialogView.findViewById(R.id.filesize));
        filetype = ((TextView)moreDialogView.findViewById(R.id.filetype));
        lastModified = ((TextView)moreDialogView.findViewById(R.id.last_modified));
        download = (LinearLayout) moreDialogView.findViewById(R.id.download);
        delete = (LinearLayout) moreDialogView.findViewById(R.id.delete);

        title.setText(filename);
        if (item.getInt(CloudListAdapter.COLUMN_TYPE) == 0){
            filesize.setText(FileUtils.byteCountToDisplaySize(item.getLong(CloudListAdapter.COLUMN_SIZE)));
            filetype.setText(item.getString(CloudListAdapter.COLUMN_MIMETYPE));
            lastModified.setText(item.getString(CloudListAdapter.COLUMN_LAST_EDITED_UTC).split("\\.")[0]);
        }

        if (item.getInt(CloudListAdapter.COLUMN_TYPE) > 0)
        {
            moreDialogView.findViewById(R.id.lay_filetype).setVisibility(View.GONE);
            moreDialogView.findViewById(R.id.lay_filesize).setVisibility(View.GONE);
            moreDialogView.findViewById(R.id.lay_last_modified).setVisibility(View.GONE);
            download.setVisibility(View.GONE);
        }

        delete.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (isSecured){
                    AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

                    builder.setView(R.layout.dialog_secured_file);
                    builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialogInterface, int i) {
                            AlertDialog dialog = (AlertDialog) dialogInterface;

                            Intent delete = new Intent(getActivity(), GrappboxJustInTimeService.class);
                            delete.setAction(GrappboxJustInTimeService.ACTION_CLOUD_DELETE);
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_FILENAME, filename);
                            if (mPath.contains("/Safe")){
                                delete.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
                            }
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mErrorReceiver);
                            delete.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_FILE_PASSWORD, ((EditText) dialog.findViewById(R.id.input_password)).getText().toString());
                            getActivity().startService(delete);
                            moreDialog.dismiss();
                        }
                    });
                    builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialogInterface, int i) {
                            dialogInterface.dismiss();
                        }
                    });
                    builder.show();
                } else{
                    Intent delete = new Intent(getActivity(), GrappboxJustInTimeService.class);
                    delete.setAction(GrappboxJustInTimeService.ACTION_CLOUD_DELETE);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_FILENAME, filename);
                    if (mPath.contains("/Safe")){
                        delete.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
                    }
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mErrorReceiver);
                    getActivity().startService(delete);
                    moreDialog.dismiss();
                }
            }
        });

        download.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                int permissionCheck = ContextCompat.checkSelfPermission(getActivity(),
                        Manifest.permission.WRITE_EXTERNAL_STORAGE);
                if (permissionCheck == PackageManager.PERMISSION_DENIED){
                    requestPermissions(new String[]{Manifest.permission.WRITE_EXTERNAL_STORAGE}, PERMISSION_REQUEST_CLOUD_DOWNLOAD);
                    moreDialog.dismiss();
                }else{
                    if (isSecured){
                        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

                        builder.setView(R.layout.dialog_secured_file);
                        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialogInterface, int i) {
                                AlertDialog dialog = (AlertDialog) dialogInterface;

                                Intent download = new Intent(getActivity(), GrappboxJustInTimeService.class);
                                download.setAction(GrappboxJustInTimeService.ACTION_CLOUD_DOWNLOAD);
                                download.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                                download.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                                download.putExtra(GrappboxJustInTimeService.EXTRA_FILENAME, filename);
                                if (mPath.contains("/Safe")){
                                    download.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
                                }
                                download.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mErrorReceiver);
                                download.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_FILE_PASSWORD, ((EditText) dialog.findViewById(R.id.input_password)).getText().toString());
                                getActivity().startService(download);
                                moreDialog.dismiss();
                            }
                        });
                        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialogInterface, int i) {
                                dialogInterface.dismiss();
                            }
                        });
                        builder.show();
                    } else{
                        Intent download = new Intent(getActivity(), GrappboxJustInTimeService.class);
                        download.setAction(GrappboxJustInTimeService.ACTION_CLOUD_DOWNLOAD);
                        download.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                        download.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PATH, mPath);
                        download.putExtra(GrappboxJustInTimeService.EXTRA_FILENAME, filename);
                        if (mPath.contains("/Safe")){
                            download.putExtra(GrappboxJustInTimeService.EXTRA_CLOUD_PASSWORD, getSafePassword());
                        }
                        download.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mErrorReceiver);
                        getActivity().startService(download);
                        moreDialog.dismiss();
                    }
                }
            }
        });

        moreDialog.setContentView(moreDialogView);
        moreDialog.show();
    }


    @Override
    public void onItemClick(AdapterView<?> adapterView, View view, final int index, long l) {
        final Cursor item = (Cursor) mCloudEntries.getItemAtPosition(index);
        if (view.getTag() instanceof CloudListAdapter.SubHeaderViewHolder)
            return;
        if (item.getString(CloudListAdapter.COLUMN_FILENAME).equals("Safe")){
            final String safePass = getSafePassword();
            if (safePass == null){
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());

                builder.setTitle(R.string.title_dialog_safe_password);
                builder.setView(R.layout.dialog_cloud_safe_access);
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int i) {
                        AlertDialog dialog = (AlertDialog) dialogInterface;
                        EditText pass = (EditText) dialog.findViewById(R.id.input_safe);
                        long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
                        if (pass == null || projectId == -1){
                            return;
                        }
                        getActivity().getSharedPreferences(CLOUD_SHARED_PREF, Context.MODE_PRIVATE).edit().putString(CLOUD_PREF_SAFE_BASE_KEY.concat(String.valueOf(projectId)), pass.getText().toString()).apply();
                        Cursor item = (Cursor) mCloudEntries.getItemAtPosition(index);

                        Bundle arg = new Bundle();
                        arg.putString(BUNDLE_KEY_CLOUD_PATH, mPath + item.getString(item.getColumnIndex(CloudEntry.COLUMN_FILENAME)) + "/");
                        arg.putString(BUNDLE_KEY_CLOUD_PASS, pass.getText().toString());
                        Fragment newFragment = new CloudFragment();

                        newFragment.setArguments(arg);
                        getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, newFragment, ProjectActivity.FRAGMENT_TAG_CLOUD).addToBackStack(null).commit();
                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int i) {
                        dialogInterface.dismiss();
                    }
                });
                builder.show();
            } else {
                Fragment newFragment = new CloudFragment();
                Bundle arg = new Bundle();
                arg.putString(BUNDLE_KEY_CLOUD_PATH, mPath + item.getString(item.getColumnIndex(CloudEntry.COLUMN_FILENAME)) + "/");
                arg.putString(BUNDLE_KEY_CLOUD_PASS, safePass);
                newFragment.setArguments(arg);
                getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, newFragment, ProjectActivity.FRAGMENT_TAG_CLOUD).addToBackStack(null).commit();
            }
        }
        else{
            if (item.getInt(CloudListAdapter.COLUMN_TYPE) == 0){
                onMoreClicked(item.getPosition());
            } else {
                Fragment newFragment = new CloudFragment();
                Bundle arg = new Bundle();
                arg.putString(BUNDLE_KEY_CLOUD_PATH, mPath + item.getString(item.getColumnIndex(CloudEntry.COLUMN_FILENAME)) + "/");
                newFragment.setArguments(arg);
                getActivity().getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, newFragment, ProjectActivity.FRAGMENT_TAG_CLOUD).addToBackStack(null).commit();
            }

        }
    }
}
