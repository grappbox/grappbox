using Grappbox.Model;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class UserViewModel : ViewModelBase
    {
        private int _id;
        private string _firstName;
        private string _lastName;
        private string _email;
        private bool _isClient;
        private int _percent;
        public int Id
        {
            get
            {
                return _id;
            }
            set
            {
                _id = value;
                NotifyPropertyChanged("Id");
            }
        }
        public string Firstname
        {
            get
            {
                return _firstName;
            }
            set
            {
                _firstName = value;
                NotifyPropertyChanged("FirstName");
            }
        }
        public string Lastname
        {
            get
            {
                return _lastName;
            }
            set
            {
                _lastName = value;
                NotifyPropertyChanged("LastName");
            }
        }
        public string Email
        {
            get
            {
                return _email;
            }
            set
            {
                _email = value;
                NotifyPropertyChanged("Email");
            }
        }
        public string Token { get; set; }
        public bool IsClient
        {
            get
            {
                return _isClient;
            }
            set
            {
                _isClient = value;
                NotifyPropertyChanged("IsClient");
            }
        }
        public int Percent
        {
            get
            {
                return _percent;
            }
            set
            {
                _percent = value;
                NotifyPropertyChanged("Percent");
            }
        }

        public string FullName
        {
            get
            {
                return Firstname + " " + Lastname;
            }
        }

        public ObservableCollection<ProjectRoleModel> RoleList
        {
            get
            {
                return ProjectSettingsViewModel.GetViewModel().RoleList;
            }
        }
        public UserViewModel UpdateUser(UserModel model)
        {
            Id = model.Id;
            Email = model.Email;
            Firstname = model.Firstname;
            Lastname = model.Lastname;
            Token = model.Token;
            IsClient = model.IsClient;
            Percent = model.Percent;
            return this;
        }
        public UserViewModel() { }
        public UserViewModel(UserModel model)
        {
            Id = model.Id;
            Email = model.Email;
            Firstname = model.Firstname;
            Lastname = model.Lastname;
            Token = model.Token;
            IsClient = model.IsClient;
            Percent = model.Percent;
        }
        public static implicit operator UserViewModel(UserModel model)
        {
            return new UserViewModel(model);
        }
    }
}