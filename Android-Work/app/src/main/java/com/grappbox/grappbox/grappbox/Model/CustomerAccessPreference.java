package com.grappbox.grappbox.grappbox.Model;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.preference.DialogPreference;
import android.preference.Preference;
import android.preference.PreferenceCategory;
import android.util.AttributeSet;
import android.util.Log;
import android.util.Xml;
import android.view.View;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 26/01/2016.
 */
public class CustomerAccessPreference extends DialogPreference {
    CustomerAccessModel _customer;
    int _projectId;
    PreferenceCategory _customer_zone = null;

    public CustomerAccessPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setPersistent(false);
    }

    public void setCustomerAccess(CustomerAccessModel model)
    {
        _customer = model;
        if (_customer == null)
            return;

        if (!_customer.isValid())
        {
            setDialogLayoutResource(R.layout.dialog_customer_access_create_pref);
            setTitle(R.string.str_customer_access_creation_title);
            setSummary("");
        }
        else
        {
            setDialogLayoutResource(R.layout.dialog_customer_access_pref);
            setTitle(_customer.getName());
            setSummary(_customer.getCustomerLoginUrl());
        }

    }

    public void setProjectId(int id)
    {
        _projectId = id;
    }

    public void setCustomerZone(PreferenceCategory customerZone)
    {
        _customer_zone = customerZone;
    }

    @Override
    protected View onCreateDialogView() {
        View v = super.onCreateDialogView();
        CustomerAccessPreference pref = this;

        if (_customer.isValid()) {
            ((ListView) v.findViewById(R.id.list_customer_access_pref)).setOnItemClickListener(new AdapterView.OnItemClickListener() {
                @Override
                public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                    String clickedItem = getContext().getResources().getStringArray(R.array.dialog_customer_access_pref_choices_enum)[position];
                    switch (clickedItem) {
                        case "copy_clipboard":
                            ClipboardManager manager = (ClipboardManager) getContext().getSystemService(Context.CLIPBOARD_SERVICE);
                            ClipData clip = ClipData.newPlainText(_customer.getName() + " access url", _customer.getCustomerLoginUrl());
                            manager.setPrimaryClip(clip);
                            Toast.makeText(getContext(), R.string.clipboard_copied, Toast.LENGTH_SHORT).show();
                            break;
                        case "renew_access":
                            GenerateCustomerAccess task = new GenerateCustomerAccess(pref, getContext(), _projectId);
                            task.execute(_customer.getName());
                            break;
                        case "delete_access":
                            DeleteCustomerAccessTask taskDelete = new DeleteCustomerAccessTask(pref, getContext());

                            taskDelete.execute();
                            break;
                        default:
                            break;
                    }
                    getDialog().dismiss();
                }
            });
        }
        return v;
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        Preference pref = this;
        if (_customer.isValid())
        {
            builder.setPositiveButton(null, null);
            return;
        }
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                Dialog dial = getDialog();
                assert dial != null;
                EditText txtName = (EditText) dial.findViewById(R.id.etxt_customer_name);
                assert txtName != null;
                GenerateCustomerAccess task = new GenerateCustomerAccess(pref, getContext(), _projectId);

                if (txtName.getText().toString().equals("")) {
                    txtName.setError(getContext().getString(R.string.str_error_customer_access_empty));
                }
                else {
                    task.execute(txtName.getText().toString());
                    dial.dismiss();
                }
            }
        });

    }

    public class GenerateCustomerAccess extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        int _projectId;
        Preference _pref;

        GenerateCustomerAccess(Preference pref, Context context, int projectId)
        {
            _context = context;
            _projectId = projectId;
            _pref = pref;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                {
                    ((MainActivity) _context).logoutUser();
                }
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            }
            else
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                    ((MainActivity) _context).logoutUser();
                return true;
            }
            return false;
        }
        @Override
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info, data;

            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                data = json.getJSONObject("data");
                assert data != null;
                GetCustomerAccessId task = new GetCustomerAccessId(_pref, _context, data.getInt("id"));

                task.execute();

                if (_customer_zone != null && !_customer.isValid())
                {
                    CustomerAccessPreference creator = new CustomerAccessPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_customer_access_create_pref)));

                    creator.setCustomerAccess(new CustomerAccessModel());
                    creator.setCustomerZone(_customer_zone);
                    _customer_zone.addPreference(creator);
                }
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            JSONObject json, data;
            String name = params[0];

            _api = APIConnectAdapter.getInstance(true);
            json = new JSONObject();
            data = new JSONObject();

            try {
                Log.e("WATCHME", String.valueOf(_projectId));
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("projectId", _projectId);
                data.put("name", name);
                json.put("data", data);
                _api.setVersion("V0.2");
                _api.startConnection("projects/generatecustomeraccess");
                _api.setRequestConnection("POST");
                _api.sendJSON(json);
                return _api.getInputSream();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public class GetCustomerAccessId extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        int _customerId;
        Preference _pref;

        GetCustomerAccessId(Preference pref, Context context, int customerId)
        {
            _context = context;
            _customerId = customerId;
            _pref = pref;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                {
                    ((MainActivity) _context).logoutUser();
                }
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            }
            else
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                    ((MainActivity) _context).logoutUser();
                return true;
            }
            return false;
        }

        @Override
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info, data;

            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                data = json.getJSONObject("data");
                assert data != null;
                _customer.setId(data.getInt("project_id"));
                _customer.setName(data.getString("name"));
                _customer.setCustomerToken(data.getString("customer_token"));
                ((CustomerAccessPreference)_pref).setDialogLayoutResource(R.layout.dialog_customer_access_pref);
                _pref.setSummary(_customer.getCustomerLoginUrl());
                _pref.setTitle(_customer.getName());
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);
            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/getcustomeraccessbyid/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_customerId));
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public class DeleteCustomerAccessTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        Preference _pref;

        DeleteCustomerAccessTask(Preference pref, Context context)
        {
            _context = context;
            _pref = pref;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                {
                    ((MainActivity) _context).logoutUser();
                }
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            }
            else
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                    ((MainActivity) _context).logoutUser();
                return true;
            }
            return false;
        }

        @Override
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info;

            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                _customer_zone.removePreference(_pref);
                return;
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);
            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/delcustomeraccess/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId) + "/" + String.valueOf(_customer.getId()));
                _api.setRequestConnection("DELETE");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }
}
