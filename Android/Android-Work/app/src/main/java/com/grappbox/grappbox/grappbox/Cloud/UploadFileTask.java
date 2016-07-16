package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.TaskStackBuilder;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.os.AsyncTask;
import android.text.TextUtils;
import android.util.Base64;
import android.util.Log;
import android.widget.Toast;

import com.github.tibolte.agendacalendarview.utils.Utils;
import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

/**
 * Created by wieser_m on 19/01/2016.
 */
public class UploadFileTask {
    public final int API_CHUNK_SIZE = 1048576;
    private static int __NEXT_ID__ = 1;
    private static int __NOTIFICATION_NEXT_ID__ = 1;

    private Context                 _context;
    private CloudFileAdapter        _adapter;
    private String                  _filename;
    private Uri                     _system_filepath;
    private int                     _chunkNumbers;
    private int                     _streamId;
    private int                     _fileSize;
    private int                     _passedChunk;
    private int                     _currentChunk;
    private String                     _projectId;
    private String                  _safePassword;
    private boolean                 _isProtected;
    private PrepareChunkTask                _chunkTask;
    private ArrayList<UploadChunkTask>      _uploadTasks;
    private Map<Integer, APIConnectAdapter> _uploadAPI;
    private APIConnectAdapter               _closeAPI;
    private NotificationManager             _notifManager;
    private int                             _notifId;
    private String                          _cloudPath;

    private enum ETask
    {
        OPEN,
        CLOSE,
        PREPARE,
        UPLOAD
    }

    UploadFileTask(CloudExplorerFragment context, CloudFileAdapter adapter, Uri system_filepath, String filename, String safePassword, int fileSize)
    {
        _context = context.getActivity().getApplicationContext();
        _adapter = adapter;
        _filename = filename;
        _system_filepath = system_filepath;
        _safePassword = safePassword;
        _fileSize = fileSize;
        _streamId = -1;
        _chunkNumbers = _fileSize / API_CHUNK_SIZE;
        if (_fileSize != _chunkNumbers *API_CHUNK_SIZE)
            ++_chunkNumbers;
        _passedChunk = 0;
        _projectId = SessionAdapter.getInstance().getCurrentSelectedProject();
        _currentChunk = -1;
        _chunkTask = null;
        _uploadTasks = new ArrayList<>();
        _uploadAPI = new HashMap<>();

        Notification.Builder builder = buildNotifBase();
        builder.setContentText(_context.getString(R.string.cloudexplorer_import_pending_text));

        _notifManager = (NotificationManager) _context.getSystemService(Context.NOTIFICATION_SERVICE);
        _notifId = __NOTIFICATION_NEXT_ID__++;
        _notifManager.notify(_notifId, builder.build());

    }

    private Notification.Builder buildNotifBase()
    {
        Notification.Builder builder = new Notification.Builder(_context);
        Intent resultIntent = new Intent(_context, MainActivity.class);
        resultIntent.putExtra(CloudExplorerFragment.CLOUDEXPLORER_PATH, _cloudPath);
        TaskStackBuilder stackBuilder =  TaskStackBuilder.create(_context);
        stackBuilder.addParentStack(MainActivity.class);
        stackBuilder.addNextIntent(resultIntent);
        PendingIntent pendingIntent = stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT);
        builder.setContentTitle(_context.getString(R.string.cloud_file_import_notification));
        builder.setContentText(_context.getString(R.string.cloudexplorer_import));
        builder.setSmallIcon(R.mipmap.icon_launcher);
        builder.setContentIntent(pendingIntent);
        return builder;
    }

    public void execute(String cloudPath, String filePassword)
    {
        OpenStreamTask task = new OpenStreamTask();

        _isProtected = !filePassword.equals("");
        _cloudPath = cloudPath;
        task.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR, cloudPath, filePassword);
    }

    private boolean handleAPIError(JSONObject infos) throws JSONException {
        if (!infos.getString("return_code").startsWith("1."))
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + infos.getString("return_code"));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            if (_chunkTask != null)
                _chunkTask.cancel(true);
            for (UploadChunkTask task: _uploadTasks) {
                task.cancel(true);
            }
            return true;
        }
        return false;
    }

    private boolean disconnectAPI(ETask task, Integer... id)
    {
        int responseCode = 500;
        APIConnectAdapter api;

        switch (task)
        {
            case OPEN:
                api = APIConnectAdapter.getInstance();
                break;
            case CLOSE:
                api = _closeAPI;
                break;
            case UPLOAD:
                if (id.length < 1)
                    return false;
                api = _uploadAPI.get(id[0]);
                break;
            default:
                api = APIConnectAdapter.getInstance();
                break;
        }
        try {
            responseCode = api.getResponseCode();
        } catch (IOException e) {
            e.printStackTrace();
        }

        if (responseCode < 300) {
            APIConnectAdapter.getInstance().closeConnection();
        }
        else
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + String.valueOf(responseCode));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.create().show();
            return true;
        }
        return false;
    }

    private class OpenStreamTask extends AsyncTask<String, Void, String>
    {

        @Override
        protected String doInBackground(String... params) {
            APIConnectAdapter api = APIConnectAdapter.getInstance();
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            String token = SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN);
            String safeURL;
            String cloudPath = params[0];
            String filePassword = params[1];
            String projectId = SessionAdapter.getInstance().getCurrentSelectedProject();

            try {
                data.put("path", cloudPath);
                if (!filePassword.equals(""))
                    data.put("password", filePassword);
                data.put("filename", _filename);
                _filename = _filename.replace(",", "");
                json.put("data", data);

                api.setVersion("V0.2");
                safeURL = (_safePassword.isEmpty() ? "" : ("/" + _safePassword));
                api.startConnection("cloud/stream/" + token + "/" + String.valueOf(projectId) + safeURL);
                api.setRequestConnection("POST");
                api.sendJSON(json);
                return api.getInputSream();
            } catch (JSONException | IOException e) {
                Log.e("WATCHME", api.toString());
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            PrepareChunkTask task = new PrepareChunkTask();
            if (disconnectAPI(ETask.OPEN))
                return;

            try {
                JSONObject json = new JSONObject(s);
                JSONObject info = json.getJSONObject("info");
                JSONObject data = json.getJSONObject("data");

                assert info != null;
                if (handleAPIError(info))
                    return;
                assert data != null;
                _streamId = data.getInt("stream_id");
                _chunkTask = task;
                Notification.Builder builder = buildNotifBase();
                builder.setProgress(_chunkNumbers, 0, false);
                _notifManager.notify(_notifId, builder.build());
                task.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR);
            } catch (JSONException e) {
                e.printStackTrace();
            }

            super.onPostExecute(s);
        }
    }

    private class CloseStreamTask extends  AsyncTask<String, Void, String>
    {
        private APIConnectAdapter _api;

        @Override
        protected String doInBackground(String... params) {
            APIConnectAdapter api = APIConnectAdapter.getInstance(true);
            _closeAPI = api;
            api.setVersion("V0.2");
            try {
                api.startConnection("cloud/stream/" + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN) + "/" + String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject()) + "/" + String.valueOf(_streamId));
                api.setRequestConnection("DELETE");
                return api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            if (disconnectAPI(ETask.CLOSE))
                return;

            try {
                JSONObject json = new JSONObject(s);
                JSONObject info = json.getJSONObject("info");

                assert info != null;
                if (handleAPIError(info))
                    return;
                if (_adapter != null) {
                    FileItem item = new FileItem();
                    item.set_type(FileItem.EFileType.FILE);
                    item.set_isSecured(_isProtected);
                    item.set_filename(_filename);
                    item.set_size(_fileSize);
                    _adapter.add(item);
                }
                Notification.Builder builder = buildNotifBase();
                builder.setContentText("Your import is finished, congratulation!");
                _notifManager.notify(_notifId, builder.build());
            } catch (JSONException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }

    private class PrepareChunkTask extends AsyncTask<String, String, String>
    {
        private int _id;
        @Override
        protected String doInBackground(String... params) {


            try {
                InputStream stream =  _context.getContentResolver().openInputStream(_system_filepath);
                Log.e("WATCH", "stream open");
                byte[] buffer = new byte[API_CHUNK_SIZE];

                assert stream != null;
                while (stream.read(buffer, 0, API_CHUNK_SIZE) != -1)
                {
                    Log.e("WATCH","READ CHUNK");
                    String buffer64 = Base64.encodeToString(buffer, Base64.DEFAULT);
                    _id = __NEXT_ID__++;
                    String chunk = buffer64;
                    int currentChunkNumber = ++_currentChunk;
                    JSONObject json = new JSONObject();
                    JSONObject data = new JSONObject();
                    APIConnectAdapter api = APIConnectAdapter.getInstance(true);
                    _uploadAPI.put(_id, api);
                    try {
                        data.put("token", SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
                        data.put("stream_id", _streamId);
                        data.put("project_id", _projectId);
                        data.put("chunk_numbers", _chunkNumbers);
                        data.put("current_chunk", currentChunkNumber);
                        data.put("file_chunk", chunk);
                        json.put("data", data);

                        api.setVersion("V0.2");
                        api.startConnection("cloud/file");
                        api.setRequestConnection("PUT");
                        api.sendJSON(json);
                        String s =  api.getInputSream();
                        ++_passedChunk;
                        if (disconnectAPI(ETask.UPLOAD, _id))
                        {
                            Log.e("WATCH", "CANCELLED");
                            cancel(true);
                            return null;
                        }

                        try {
                            json = new JSONObject(s);
                            JSONObject info = json.getJSONObject("info");

                            assert info != null;
                            if (handleAPIError(info)) {
                                Log.e("WATCH", "CANCELLED");
                                cancel(true);
                                return null;
                            }
                            Notification.Builder builder = buildNotifBase();
                            builder.setProgress(_chunkNumbers, _passedChunk, false);
                            _notifManager.notify(_notifId, builder.build());

                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    } catch (JSONException | IOException e) {
                        e.printStackTrace();

//                        Toast.makeText(_adapter.getContext(), "The upload failed", Toast.LENGTH_SHORT).show();
                    }
                }
                return _uploadAPI.get(_id).getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            CloseStreamTask task = new CloseStreamTask();

            task.executeOnExecutor(AsyncTask.THREAD_POOL_EXECUTOR);
            super.onPostExecute(s);
        }

        @Override
        protected void onProgressUpdate(String... values) {
            if (values.length < 1)
                return;
            String chunk = values[0];
            UploadChunkTask task = new UploadChunkTask();

            _uploadTasks.add(task);
            ++_currentChunk;
            task.execute(chunk, String.valueOf(_currentChunk));
            super.onProgressUpdate(values);
        }
    }

    private class UploadChunkTask extends  AsyncTask<String, Void, String>
    {
        private int _id;


        @Override
        protected String doInBackground(String... params) {
            if (params.length < 2)
                return null;
            _id = __NEXT_ID__++;
            String chunk = params[0];
            int currentChunkNumber = Integer.getInteger(params[1]);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            APIConnectAdapter api = APIConnectAdapter.getInstance(true);
            _uploadAPI.put(_id, api);
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("stream_id", _streamId);
                data.put("project_id", _projectId);
                data.put("chunk_numbers", _chunkNumbers);
                data.put("current_chunk", currentChunkNumber);
                data.put("file_chunk", chunk);
                json.put("data", data);

                api.setVersion("V0.2");
                api.startConnection("cloud/file");
                api.setRequestConnection("PUT");
                api.sendJSON(json);
                return api.getInputSream();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            ++_passedChunk;
            if (disconnectAPI(ETask.UPLOAD, _id))
                return;

            try {
                JSONObject json = new JSONObject(s);
                JSONObject info = json.getJSONObject("info");

                assert info != null;
                if (handleAPIError(info))
                    return;
                Notification.Builder builder = buildNotifBase();
                builder.setProgress(_chunkNumbers, _passedChunk, false);
                _notifManager.notify(_notifId, builder.build());
                if (_passedChunk == _chunkNumbers)
                {
                    CloseStreamTask task = new CloseStreamTask();

                    task.execute();
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }
}
