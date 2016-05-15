package com.grappbox.grappbox.grappbox.Cloud;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.provider.OpenableColumns;
import android.support.v4.app.Fragment;
import android.text.TextUtils;
import android.text.method.PasswordTransformationMethod;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ListView;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class CloudExplorerFragment extends Fragment {
    // the fragment initialization parameters, e.g. ARG_ITEM_NUMBER
    private static final String _APISafeDirectoryPath = "/Safe";
    public static final String CLOUDEXPLORER_PATH = "key_cloudexplorer_path";
    private static String _path;
    private CloudFileAdapter _adapter;
    private GetCloudFileListTask _currentLSTask = null;
    private String _safePassword = "";
    CloudExplorerFragment _childrenContext;

    public CloudExplorerFragment() {
        _path = "/";
    }

    public void setPath(String path)
    {
        _path = path;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        _childrenContext = this;
    }
    public void setSafePassword(String password)
    {
        _safePassword = password;
    }

    public void resetPath()
    {
        _path = "/";
    }

    private void handleSafe(GetCloudFileListTask currentTask)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        AlertDialog dialog = null;
        _currentLSTask = currentTask;
        EditText passView = new EditText(getActivity());

        passView.setTransformationMethod(new PasswordTransformationMethod());
        passView.setId(R.id.cloudexplorer_safepassword_view);
        builder.setCancelable(true);
        builder.setTitle(R.string.safe_password_question);
        builder.setView(passView);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {

            @Override
            public void onClick(DialogInterface dialog, int which) {
                EditText passview = (EditText) ((AlertDialog) dialog).findViewById(R.id.cloudexplorer_safepassword_view);

                _safePassword = passview.getText().toString();
                _currentLSTask.execute(_path, _safePassword);
                _currentLSTask = null;
            }
        });

        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {

            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });

        builder.setOnCancelListener(new DialogInterface.OnCancelListener() {
            @Override
            public void onCancel(DialogInterface dialog) {
                _currentLSTask = null;
                goToParent();
            }
        });
        dialog = builder.create();
        dialog.show();
    }

    public void goToParent()
    {
        List<String> list = new ArrayList<>();
        String[] path = _path.split("/");
        Collections.addAll(list, path);

        list.remove(list.size() - 1);
        _path = TextUtils.join("/", list.toArray());
    }

    public void createDirectory()
    {
        final AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        LayoutInflater inflater = getLayoutInflater(null);
        View dialogView = inflater.inflate(R.layout.cloudexplorer_alertdialog_createdir, null);

        builder.setCancelable(true);
        builder.setTitle("Create new folder");
        builder.setView(dialogView);
        builder.setPositiveButton(R.string.positive_response, null);
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });

        final AlertDialog dialog = builder.show();
        assert dialog != null;
        final EditText txtEdit = (EditText) dialogView.findViewById(R.id.folder_name);
        assert txtEdit != null;

        dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                CreateDirectoryTask task = new CreateDirectoryTask(_childrenContext, _adapter);
                if (txtEdit.getText().toString().contains("/")) {
                    txtEdit.setError(getContext().getString(R.string.error_folder_incorrect_characters));
                    return;
                }
                task.execute(_path, txtEdit.getText().toString(), _safePassword);
                dialog.dismiss();
            }
        });
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (resultCode != Activity.RESULT_OK)
            return;

        switch (requestCode)
        {
            case MainActivity.PICK_DOCUMENT_FROM_SYSTEM:
                fileSelectedResult(data, false);
                break;
            case MainActivity.PICK_DOCUMENT_SECURED_FROM_SYSTEM:
                fileSelectedResult(data, true);
                break;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }

    public void fileSelectedResult(Intent intent, boolean isSecured)
    {
        Uri uri = intent.getData();
        Cursor fileStat = getActivity().getContentResolver().query(uri, null, null, null, null);
        assert fileStat != null;
        int filename_index = fileStat.getColumnIndex(OpenableColumns.DISPLAY_NAME);
        int size_index = fileStat.getColumnIndex(OpenableColumns.SIZE);
        fileStat.moveToFirst();
        String filename = fileStat.getString(filename_index);
        int size = fileStat.getInt(size_index);
        fileStat.close();

        final UploadFileTask task = new UploadFileTask(this, _adapter, uri, filename, _safePassword, size);
        if (isSecured)
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(getContext());
            EditText txtFilePassword = new EditText(getContext());
            txtFilePassword.setHint(R.string.cloudexplorer_filepassword);
            txtFilePassword.setTransformationMethod(new PasswordTransformationMethod());
            txtFilePassword.setId(R.id.cloudexplorer_filepassword_view);
            builder.setTitle(R.string.set_file_password);
            builder.setView(txtFilePassword);
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    AlertDialog alert = (AlertDialog) dialog;
                    EditText textView = (EditText) alert.findViewById(R.id.cloudexplorer_filepassword_view);

                    task.execute(_path, textView.getText().toString());
                }
            });
            builder.show();
        }
        else
        {
            task.execute(_path, "");
        }
    }

    public void importFile()
    {
        Intent intent = new Intent(Intent.ACTION_OPEN_DOCUMENT);
        intent.setType("*/*");
        intent.addCategory(Intent.CATEGORY_OPENABLE);
        startActivityForResult(intent, MainActivity.PICK_DOCUMENT_FROM_SYSTEM);

    }

    public void importFileSecure()
    {
        Intent intent = new Intent(Intent.ACTION_OPEN_DOCUMENT);
        intent.setType("*/*");
        intent.addCategory(Intent.CATEGORY_OPENABLE);
        startActivityForResult(intent, MainActivity.PICK_DOCUMENT_SECURED_FROM_SYSTEM);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View view = inflater.inflate(R.layout.fragment_cloud_explorer, container, false);
        ListView list = (ListView) view.findViewById(R.id.cloudexplorer_itemlist);
        final CloudFileAdapter adapter = new CloudFileAdapter(getContext(), R.id.cloudexplorer_item_filename);
        ImageButton btnCreateDir = (ImageButton) view.findViewById(R.id.btn_createDir);
        ImageButton btnUpload = (ImageButton) view.findViewById(R.id.btn_import);
        ImageButton btnUploadSecure = (ImageButton) view.findViewById(R.id.btn_import_secure);

        _adapter = adapter;
        GetCloudFileListTask task = new GetCloudFileListTask(this, adapter);
        if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
            handleSafe(task);
        else
            task.execute(_path, _safePassword);
        list.setAdapter(adapter);

        btnCreateDir.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                createDirectory();
            }
        });
        btnUpload.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                importFile();
            }
        });
        btnUploadSecure.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                importFileSecure();
            }
        });

        list.setOnItemLongClickListener(new AdapterView.OnItemLongClickListener() {
            @Override
            public boolean onItemLongClick(AdapterView<?> parent, View view, int position, long id) {
                CloudFileAdapter adapter1 = (CloudFileAdapter) parent.getAdapter();
                final FileItem clickedItem = adapter1.getItem(position);
                if (clickedItem.get_type() == FileItem.EFileType.DIR && !clickedItem.get_filename().equals("Safe"))
                {
                    AlertDialog.Builder dialogBuilder = new AlertDialog.Builder(getActivity());
                    dialogBuilder.setItems(R.array.cloudExplorer_dirAction, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            DeleteFileTask task  = new DeleteFileTask(_childrenContext, _adapter, clickedItem);
                            task.execute(_path, _safePassword);
                        }
                    });
                    dialogBuilder.show();
                    return true;
                }
                return false;
            }
        });

        list.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                CloudFileAdapter adapter1 = (CloudFileAdapter) parent.getAdapter();
                final FileItem clickedItem = adapter1.getItem(position);

                if (clickedItem.get_type() == FileItem.EFileType.BACK)
                {
                    goToParent();
                    if (_path == "")
                        _path = "/";
                    GetCloudFileListTask task = new GetCloudFileListTask(_childrenContext, _adapter);
                    if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
                        handleSafe(task);
                    else
                        task.execute(_path, _safePassword);
                }
                else if (clickedItem.get_type() == FileItem.EFileType.DIR) {
                    if (_path == "/")
                        _path += clickedItem.get_filename();
                    else
                        _path += ("/" + clickedItem.get_filename());
                    GetCloudFileListTask task = new GetCloudFileListTask(_childrenContext, _adapter);
                    if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
                        handleSafe(task);
                    else
                        task.execute(_path, _safePassword);
                } else {
                    AlertDialog.Builder dialogBuilder = new AlertDialog.Builder(getActivity());
                    dialogBuilder.setItems(R.array.cloudExplorer_fileAction, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            switch (which)
                            {
                                case 0:
                                    if (!clickedItem.isSecured())
                                    {
                                        DownloadFileTask task  = new DownloadFileTask(getActivity().getApplicationContext());
                                        task.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR,_path + "," + clickedItem.get_filename(), _safePassword, clickedItem.get_filename());
                                    }
                                    else
                                    {
                                        AlertDialog.Builder builder = new AlertDialog.Builder(getContext());
                                        EditText txtFilePassword = new EditText(getContext());
                                        txtFilePassword.setHint(R.string.cloudexplorer_filepassword);
                                        txtFilePassword.setTransformationMethod(new PasswordTransformationMethod());
                                        txtFilePassword.setId(R.id.cloudexplorer_filepassword_view);
                                        builder.setTitle(R.string.set_file_password);
                                        builder.setView(txtFilePassword);
                                        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                                            @Override
                                            public void onClick(DialogInterface dialog, int which) {
                                                DownloadFileSecuredTask task = new DownloadFileSecuredTask(getActivity().getApplicationContext(), getActivity());
                                                AlertDialog alert = (AlertDialog) dialog;
                                                EditText textView = (EditText) alert.findViewById(R.id.cloudexplorer_filepassword_view);

                                                task.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR, _path + "," + clickedItem.get_filename(), textView.getText().toString(), _safePassword, clickedItem.get_filename());
                                            }
                                        });
                                        builder.show();
                                    }
                                    break;
                                case 1:
                                    if (!clickedItem.isSecured())
                                    {
                                        DeleteFileTask task  = new DeleteFileTask(_childrenContext, _adapter, clickedItem);
                                        task.execute(_path, _safePassword);
                                    }
                                    else
                                    {
                                        AlertDialog.Builder builder = new AlertDialog.Builder(getContext());
                                        EditText txtFilePassword = new EditText(getContext());
                                        txtFilePassword.setHint(R.string.cloudexplorer_filepassword);
                                        txtFilePassword.setTransformationMethod(new PasswordTransformationMethod());
                                        txtFilePassword.setId(R.id.cloudexplorer_filepassword_view);
                                        builder.setTitle(R.string.set_file_password);
                                        builder.setView(txtFilePassword);
                                        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                                            @Override
                                            public void onClick(DialogInterface dialog, int which) {
                                                DeleteFileSecureTask task = new DeleteFileSecureTask(_childrenContext, _adapter, clickedItem);
                                                AlertDialog alert = (AlertDialog) dialog;
                                                EditText textView = (EditText) alert.findViewById(R.id.cloudexplorer_filepassword_view);

                                                task.execute(_path, textView.getText().toString(), _safePassword);
                                            }
                                        });
                                        builder.show();
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    });
                    dialogBuilder.create().show();
                }
            }

        });

        return view;
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }
}
