<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appDevDebugProjectContainerUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appDevDebugProjectContainerUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/_')) {
            // _wdt
            if (0 === strpos($pathinfo, '/_wdt') && preg_match('#^/_wdt/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_wdt')), array (  '_controller' => 'web_profiler.controller.profiler:toolbarAction',));
            }

            if (0 === strpos($pathinfo, '/_profiler')) {
                // _profiler_home
                if (rtrim($pathinfo, '/') === '/_profiler') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_profiler_home');
                    }

                    return array (  '_controller' => 'web_profiler.controller.profiler:homeAction',  '_route' => '_profiler_home',);
                }

                if (0 === strpos($pathinfo, '/_profiler/search')) {
                    // _profiler_search
                    if ($pathinfo === '/_profiler/search') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchAction',  '_route' => '_profiler_search',);
                    }

                    // _profiler_search_bar
                    if ($pathinfo === '/_profiler/search_bar') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchBarAction',  '_route' => '_profiler_search_bar',);
                    }

                }

                // _profiler_info
                if (0 === strpos($pathinfo, '/_profiler/info') && preg_match('#^/_profiler/info/(?P<about>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_info')), array (  '_controller' => 'web_profiler.controller.profiler:infoAction',));
                }

                // _profiler_phpinfo
                if ($pathinfo === '/_profiler/phpinfo') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:phpinfoAction',  '_route' => '_profiler_phpinfo',);
                }

                // _profiler_search_results
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/search/results$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_search_results')), array (  '_controller' => 'web_profiler.controller.profiler:searchResultsAction',));
                }

                // _profiler
                if (preg_match('#^/_profiler/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler')), array (  '_controller' => 'web_profiler.controller.profiler:panelAction',));
                }

                // _profiler_router
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/router$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_router')), array (  '_controller' => 'web_profiler.controller.router:panelAction',));
                }

                // _profiler_exception
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception')), array (  '_controller' => 'web_profiler.controller.exception:showAction',));
                }

                // _profiler_exception_css
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception\\.css$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception_css')), array (  '_controller' => 'web_profiler.controller.exception:cssAction',));
                }

            }

            // _twig_error_test
            if (0 === strpos($pathinfo, '/_error') && preg_match('#^/_error/(?P<code>\\d+)(?:\\.(?P<_format>[^/]++))?$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_twig_error_test')), array (  '_controller' => 'twig.controller.preview_error:previewErrorPageAction',  '_format' => 'html',));
            }

        }

        if (0 === strpos($pathinfo, '/mongo')) {
            // mongo_user_passwordEncrypt
            if (0 === strpos($pathinfo, '/mongo/user/passwordencrypt') && preg_match('#^/mongo/user/passwordencrypt/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_mongo_user_passwordEncrypt;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_passwordEncrypt')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::passwordEncryptAction',  '_format' => 'json',));
            }
            not_mongo_user_passwordEncrypt:

            if (0 === strpos($pathinfo, '/mongo/account')) {
                // mongo_accountAdministration_preorder
                if ($pathinfo === '/mongo/account/preorder') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_accountAdministration_preorder;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\AccountAdministrationController::preorderAction',  '_format' => 'json',  '_route' => 'mongo_accountAdministration_preorder',);
                }
                not_mongo_accountAdministration_preorder:

                if (0 === strpos($pathinfo, '/mongo/account/log')) {
                    // mongo_accountAdministration_login
                    if ($pathinfo === '/mongo/account/login') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_accountAdministration_login;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\AccountAdministrationController::loginAction',  '_route' => 'mongo_accountAdministration_login',);
                    }
                    not_mongo_accountAdministration_login:

                    // mongo_accountAdministration_logout
                    if ($pathinfo === '/mongo/account/logout') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_accountAdministration_logout;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\AccountAdministrationController::logoutAction',  '_route' => 'mongo_accountAdministration_logout',);
                    }
                    not_mongo_accountAdministration_logout:

                }

                // mongo_accountAdministration_register
                if ($pathinfo === '/mongo/account/register') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_accountAdministration_register;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\AccountAdministrationController::registerAction',  '_route' => 'mongo_accountAdministration_register',);
                }
                not_mongo_accountAdministration_register:

            }

            if (0 === strpos($pathinfo, '/mongo/bugtracker')) {
                if (0 === strpos($pathinfo, '/mongo/bugtracker/ticket')) {
                    // mongo_bugtracker_postTicket
                    if ($pathinfo === '/mongo/bugtracker/ticket') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_bugtracker_postTicket;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::postTicketAction',  '_route' => 'mongo_bugtracker_postTicket',);
                    }
                    not_mongo_bugtracker_postTicket:

                    // mongo_bugtracker_editTicket
                    if (preg_match('#^/mongo/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_bugtracker_editTicket;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_editTicket')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::editTicketAction',));
                    }
                    not_mongo_bugtracker_editTicket:

                    // mongo_bugtracker_closeTicket
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/ticket/close') && preg_match('#^/mongo/bugtracker/ticket/close/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_bugtracker_closeTicket;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_closeTicket')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::closeTicketAction',));
                    }
                    not_mongo_bugtracker_closeTicket:

                    // mongo_bugtracker_reopenTicket
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/ticket/reopen') && preg_match('#^/mongo/bugtracker/ticket/reopen/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_reopenTicket;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_reopenTicket')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::reopenTicketAction',));
                    }
                    not_mongo_bugtracker_reopenTicket:

                    // mongo_bugtracker_deleteTicket
                    if (preg_match('#^/mongo/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_bugtracker_deleteTicket;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_deleteTicket')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::deleteTicketAction',));
                    }
                    not_mongo_bugtracker_deleteTicket:

                    // mongo_bugtracker_getTicket
                    if (preg_match('#^/mongo/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_getTicket;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getTicket')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getTicketAction',));
                    }
                    not_mongo_bugtracker_getTicket:

                    if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets')) {
                        // mongo_bugtracker_getTickets
                        if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets/opened') && preg_match('#^/mongo/bugtracker/tickets/opened/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_bugtracker_getTickets;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getTickets')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getTicketsAction',));
                        }
                        not_mongo_bugtracker_getTickets:

                        // mongo_bugtracker_getClosedTickets
                        if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets/closed') && preg_match('#^/mongo/bugtracker/tickets/closed/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_bugtracker_getClosedTickets;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getClosedTickets')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getClosedTicketsAction',));
                        }
                        not_mongo_bugtracker_getClosedTickets:

                        // mongo_bugtracker_getLastTickets
                        if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets/opened') && preg_match('#^/mongo/bugtracker/tickets/opened/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_bugtracker_getLastTickets;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getLastTickets')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getLastTicketsAction',  '_format' => 'json',));
                        }
                        not_mongo_bugtracker_getLastTickets:

                        // mongo_bugtracker_getLastClosedTickets
                        if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets/closed') && preg_match('#^/mongo/bugtracker/tickets/closed/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_bugtracker_getLastClosedTickets;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getLastClosedTickets')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getLastClosedTicketsAction',  '_format' => 'json',));
                        }
                        not_mongo_bugtracker_getLastClosedTickets:

                        // mongo_bugtracker_getTicketsByUser
                        if (0 === strpos($pathinfo, '/mongo/bugtracker/tickets/user') && preg_match('#^/mongo/bugtracker/tickets/user/(?P<id>\\d+)/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_bugtracker_getTicketsByUser;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getTicketsByUser')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getTicketsByUserAction',  '_format' => 'json',));
                        }
                        not_mongo_bugtracker_getTicketsByUser:

                    }

                }

                // mongo_bugtracker_setParticipants
                if (0 === strpos($pathinfo, '/mongo/bugtracker/users') && preg_match('#^/mongo/bugtracker/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_bugtracker_setParticipants;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_setParticipants')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::setParticipantsAction',  '_format' => 'json',));
                }
                not_mongo_bugtracker_setParticipants:

                if (0 === strpos($pathinfo, '/mongo/bugtracker/comment')) {
                    // mongo_bugtracker_getComments
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/comments') && preg_match('#^/mongo/bugtracker/comments/(?P<ticketId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_getComments;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getComments')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getCommentsAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_getComments:

                    // mongo_bugtracker_postComment
                    if ($pathinfo === '/mongo/bugtracker/comment') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_bugtracker_postComment;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::postCommentAction',  '_format' => 'json',  '_route' => 'mongo_bugtracker_postComment',);
                    }
                    not_mongo_bugtracker_postComment:

                    // mongo_bugtracker_editComment
                    if (preg_match('#^/mongo/bugtracker/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_bugtracker_editComment;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_editComment')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::editCommentAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_editComment:

                    // mongo_bugtracker_deleteComment
                    if (preg_match('#^/mongo/bugtracker/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_bugtracker_deleteComment;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_deleteComment')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::deleteCommentAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_deleteComment:

                }

                if (0 === strpos($pathinfo, '/mongo/bugtracker/tag')) {
                    // mongo_bugtracker_tagCreation
                    if ($pathinfo === '/mongo/bugtracker/tag') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_bugtracker_tagCreation;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::tagCreationAction',  '_format' => 'json',  '_route' => 'mongo_bugtracker_tagCreation',);
                    }
                    not_mongo_bugtracker_tagCreation:

                    // mongo_bugtracker_tagUpdate
                    if (preg_match('#^/mongo/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_bugtracker_tagUpdate;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_tagUpdate')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::tagUpdateAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_tagUpdate:

                    // mongo_bugtracker_getTagInfos
                    if (preg_match('#^/mongo/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_getTagInfos;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getTagInfos')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getTagInfosAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_getTagInfos:

                    // mongo_bugtracker_deleteTag
                    if (preg_match('#^/mongo/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_bugtracker_deleteTag;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_deleteTag')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::deleteTagAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_deleteTag:

                    // mongo_bugtracker_assignTag
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/tag/assign') && preg_match('#^/mongo/bugtracker/tag/assign/(?P<bugId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_bugtracker_assignTag;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_assignTag')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::assignTagAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_assignTag:

                    // mongo_bugtracker_removeTag
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/tag/remove') && preg_match('#^/mongo/bugtracker/tag/remove/(?P<bugId>\\d+)/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_bugtracker_removeTag;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_removeTag')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::removeTagAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_removeTag:

                }

                // mongo_bugtracker_getProjectTags
                if (0 === strpos($pathinfo, '/mongo/bugtracker/project/tags') && preg_match('#^/mongo/bugtracker/project/tags/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_bugtracker_getProjectTags;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getProjectTags')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getProjectTagsAction',  '_format' => 'json',));
                }
                not_mongo_bugtracker_getProjectTags:

                if (0 === strpos($pathinfo, '/mongo/bugtracker/get')) {
                    // mongo_bugtracker_getTicketsByState
                    if (0 === strpos($pathinfo, '/mongo/bugtracker/getticketsbystate') && preg_match('#^/mongo/bugtracker/getticketsbystate/(?P<id>\\d+)/(?P<state>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_getTicketsByState;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_bugtracker_getTicketsByState')), array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getTicketsByStateAction',  '_format' => 'json',));
                    }
                    not_mongo_bugtracker_getTicketsByState:

                    // mongo_bugtracker_getStates
                    if ($pathinfo === '/mongo/bugtracker/getstates') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_bugtracker_getStates;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\BugtrackerController::getStatesAction',  '_format' => 'json',  '_route' => 'mongo_bugtracker_getStates',);
                    }
                    not_mongo_bugtracker_getStates:

                }

            }

            if (0 === strpos($pathinfo, '/mongo/cloud')) {
                if (0 === strpos($pathinfo, '/mongo/cloud/stream')) {
                    // mongo_cloud_streamOpenAction
                    if (preg_match('#^/mongo/cloud/stream/(?P<idProject>[^/]++)(?:/(?P<safePassword>[^/]++))?$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_cloud_streamOpenAction;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_streamOpenAction')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::openStreamAction',  'safePassword' => NULL,  '_format' => 'json',));
                    }
                    not_mongo_cloud_streamOpenAction:

                    // mongo_cloud_streamCloseAction
                    if (preg_match('#^/mongo/cloud/stream/(?P<projectId>[^/]++)/(?P<streamId>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_cloud_streamCloseAction;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_streamCloseAction')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::closeStreamAction',  '_format' => 'json',));
                    }
                    not_mongo_cloud_streamCloseAction:

                }

                // mongo_cloud_sendFile
                if ($pathinfo === '/mongo/cloud/file') {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_cloud_sendFile;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\CloudController::sendFileAction',  '_format' => 'json',  '_route' => 'mongo_cloud_sendFile',);
                }
                not_mongo_cloud_sendFile:

                // mongo_cloud_getList
                if (0 === strpos($pathinfo, '/mongo/cloud/list') && preg_match('#^/mongo/cloud/list/(?P<idProject>[^/]++)/(?P<path>[^/]++)(?:/(?P<password>[^/]++))?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_cloud_getList;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_getList')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::getListAction',  'password' => NULL,  '_format' => 'json',));
                }
                not_mongo_cloud_getList:

                if (0 === strpos($pathinfo, '/mongo/cloud/file')) {
                    // mongo_cloud_getFile
                    if (preg_match('#^/mongo/cloud/file/(?P<cloudPath>[^/]++)/(?P<idProject>[^/]++)(?:/(?P<passwordSafe>[^/]++))?$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_cloud_getFile;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_getFile')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::getFileAction',  'passwordSafe' => NULL,));
                    }
                    not_mongo_cloud_getFile:

                    // mongo_cloud_getFile_secured
                    if (0 === strpos($pathinfo, '/mongo/cloud/filesecured') && preg_match('#^/mongo/cloud/filesecured/(?P<cloudPath>[^/]++)/(?P<idProject>[^/]++)/(?P<password>[^/]++)(?:/(?P<passwordSafe>[^/]++))?$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_cloud_getFile_secured;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_getFile_secured')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::getFileSecuredAction',  'passwordSafe' => NULL,));
                    }
                    not_mongo_cloud_getFile_secured:

                }

                // mongo_cloud_setSafePass
                if ($pathinfo === '/mongo/cloud/safepass') {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_cloud_setSafePass;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\CloudController::setSafePassAction',  '_route' => 'mongo_cloud_setSafePass',);
                }
                not_mongo_cloud_setSafePass:

                // mongo_cloud_delete
                if (0 === strpos($pathinfo, '/mongo/cloud/file') && preg_match('#^/mongo/cloud/file/(?P<projectId>[^/]++)/(?P<path>[^/]++)(?:/(?P<password>[^/]++))?$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_cloud_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_delete')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::delAction',  'password' => NULL,  '_format' => 'json',));
                }
                not_mongo_cloud_delete:

                // mongo_cloud_createDir
                if ($pathinfo === '/mongo/cloud/createdir') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_cloud_createDir;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\CloudController::createDirAction',  '_format' => 'json',  '_route' => 'mongo_cloud_createDir',);
                }
                not_mongo_cloud_createDir:

                // mongo_cloud_delete_secured
                if (0 === strpos($pathinfo, '/mongo/cloud/filesecured') && preg_match('#^/mongo/cloud/filesecured/(?P<projectId>[^/]++)/(?P<path>[^/]++)/(?P<password>[^/]++)(?:/(?P<safe_password>[^/]++))?$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_cloud_delete_secured;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_cloud_delete_secured')), array (  '_controller' => 'MongoBundle\\Controller\\CloudController::delSecuredAction',  'safe_password' => NULL,  '_format' => 'json',));
                }
                not_mongo_cloud_delete_secured:

            }

            if (0 === strpos($pathinfo, '/mongo/dashboard')) {
                // mongo_dashboard_getTeamOccupation
                if (0 === strpos($pathinfo, '/mongo/dashboard/occupation') && preg_match('#^/mongo/dashboard/occupation/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_dashboard_getTeamOccupation;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_dashboard_getTeamOccupation')), array (  '_controller' => 'MongoBundle\\Controller\\DashboardController::getTeamOccupationAction',  '_format' => 'json',));
                }
                not_mongo_dashboard_getTeamOccupation:

                // mongo_dashboard_getNextMeetings
                if (0 === strpos($pathinfo, '/mongo/dashboard/meetings') && preg_match('#^/mongo/dashboard/meetings/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_dashboard_getNextMeetings;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_dashboard_getNextMeetings')), array (  '_controller' => 'MongoBundle\\Controller\\DashboardController::getNextMeetingsAction',  '_format' => 'json',));
                }
                not_mongo_dashboard_getNextMeetings:

                // mongo_dashboard_getProjectsGlobalProgress
                if ($pathinfo === '/mongo/dashboard/projects') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_dashboard_getProjectsGlobalProgress;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\DashboardController::getProjectsGlobalProgressAction',  '_format' => 'json',  '_route' => 'mongo_dashboard_getProjectsGlobalProgress',);
                }
                not_mongo_dashboard_getProjectsGlobalProgress:

            }

            if (0 === strpos($pathinfo, '/mongo/event')) {
                // mongo_event_postEvent
                if ($pathinfo === '/mongo/event') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_event_postEvent;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\EventController::postEventAction',  '_format' => 'json',  '_route' => 'mongo_event_postEvent',);
                }
                not_mongo_event_postEvent:

                // mongo_event_editEvent
                if (preg_match('#^/mongo/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_event_editEvent;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_event_editEvent')), array (  '_controller' => 'MongoBundle\\Controller\\EventController::editEventAction',  '_format' => 'json',));
                }
                not_mongo_event_editEvent:

                // mongo_event_delEvent
                if (preg_match('#^/mongo/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_event_delEvent;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_event_delEvent')), array (  '_controller' => 'MongoBundle\\Controller\\EventController::delEventAction',  '_format' => 'json',));
                }
                not_mongo_event_delEvent:

                // mongo_event_getEvent
                if (preg_match('#^/mongo/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_event_getEvent;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_event_getEvent')), array (  '_controller' => 'MongoBundle\\Controller\\EventController::getEventAction',  '_format' => 'json',));
                }
                not_mongo_event_getEvent:

                // mongo_event_setParticipants
                if (0 === strpos($pathinfo, '/mongo/event/users') && preg_match('#^/mongo/event/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_event_setParticipants;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_event_setParticipants')), array (  '_controller' => 'MongoBundle\\Controller\\EventController::setParticipantsAction',  '_format' => 'json',));
                }
                not_mongo_event_setParticipants:

            }

            if (0 === strpos($pathinfo, '/mongo/notification')) {
                if (0 === strpos($pathinfo, '/mongo/notification/device')) {
                    // mongo_notification_registerDevice
                    if ($pathinfo === '/mongo/notification/device') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_notification_registerDevice;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\NotificationController::registerDeviceAction',  '_format' => 'json',  '_route' => 'mongo_notification_registerDevice',);
                    }
                    not_mongo_notification_registerDevice:

                    // mongo_notification_getUserDevices
                    if ($pathinfo === '/mongo/notification/devices') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_notification_getUserDevices;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\NotificationController::getUserDevicesAction',  '_format' => 'json',  '_route' => 'mongo_notification_getUserDevices',);
                    }
                    not_mongo_notification_getUserDevices:

                }

                // mongo_notification_getNotifications
                if (preg_match('#^/mongo/notification/(?P<read>[a-zA-Z0-9]+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_notification_getNotifications;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_notification_getNotifications')), array (  '_controller' => 'MongoBundle\\Controller\\NotificationController::getNotificationsAction',  '_format' => 'json',));
                }
                not_mongo_notification_getNotifications:

                // mongo_notification_setNotificationToRead
                if (0 === strpos($pathinfo, '/mongo/notification/setnotificationtoread') && preg_match('#^/mongo/notification/setnotificationtoread/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_notification_setNotificationToRead;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_notification_setNotificationToRead')), array (  '_controller' => 'MongoBundle\\Controller\\NotificationController::setNotificationToReadAction',  '_format' => 'json',));
                }
                not_mongo_notification_setNotificationToRead:

            }

            if (0 === strpos($pathinfo, '/mongo/p')) {
                if (0 === strpos($pathinfo, '/mongo/planning')) {
                    // mongo_planning_getDayPlanning
                    if (0 === strpos($pathinfo, '/mongo/planning/day') && preg_match('#^/mongo/planning/day/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_planning_getDayPlanning;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_planning_getDayPlanning')), array (  '_controller' => 'MongoBundle\\Controller\\PlanningController::getDayPlanningAction',  '_format' => 'json',));
                    }
                    not_mongo_planning_getDayPlanning:

                    // mongo_planning_getWeekPlanning
                    if (0 === strpos($pathinfo, '/mongo/planning/week') && preg_match('#^/mongo/planning/week/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_planning_getWeekPlanning;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_planning_getWeekPlanning')), array (  '_controller' => 'MongoBundle\\Controller\\PlanningController::getWeekPlanningAction',  '_format' => 'json',));
                    }
                    not_mongo_planning_getWeekPlanning:

                    // mongo_planning_getMonthPlanning
                    if (0 === strpos($pathinfo, '/mongo/planning/month') && preg_match('#^/mongo/planning/month/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_planning_getMonthPlanning;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_planning_getMonthPlanning')), array (  '_controller' => 'MongoBundle\\Controller\\PlanningController::getMonthPlanningAction',  '_format' => 'json',));
                    }
                    not_mongo_planning_getMonthPlanning:

                }

                if (0 === strpos($pathinfo, '/mongo/project')) {
                    // mongo_project_projectCreation
                    if ($pathinfo === '/mongo/project') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_project_projectCreation;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::projectCreationAction',  '_format' => 'json',  '_route' => 'mongo_project_projectCreation',);
                    }
                    not_mongo_project_projectCreation:

                    // mongo_project_updateInformations
                    if (preg_match('#^/mongo/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_project_updateInformations;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_updateInformations')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::updateInformationsAction',  '_format' => 'json',));
                    }
                    not_mongo_project_updateInformations:

                    // mongo_project_getInformations
                    if (preg_match('#^/mongo/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_project_getInformations;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_getInformations')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::getInformationsAction',  '_format' => 'json',));
                    }
                    not_mongo_project_getInformations:

                    // mongo_project_delProject
                    if (preg_match('#^/mongo/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_project_delProject;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_delProject')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::delProjectAction',  '_format' => 'json',));
                    }
                    not_mongo_project_delProject:

                    // mongo_project_retrieveProject
                    if (0 === strpos($pathinfo, '/mongo/project/retrieve') && preg_match('#^/mongo/project/retrieve/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_project_retrieveProject;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_retrieveProject')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::retrieveProjectAction',  '_format' => 'json',));
                    }
                    not_mongo_project_retrieveProject:

                    if (0 === strpos($pathinfo, '/mongo/project/customeraccess')) {
                        // mongo_project_generateCustomerAccess
                        if ($pathinfo === '/mongo/project/customeraccess') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_mongo_project_generateCustomerAccess;
                            }

                            return array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::generateCustomerAccessAction',  '_format' => 'json',  '_route' => 'mongo_project_generateCustomerAccess',);
                        }
                        not_mongo_project_generateCustomerAccess:

                        // mongo_project_getCustomerAccessByProject
                        if (0 === strpos($pathinfo, '/mongo/project/customeraccesses') && preg_match('#^/mongo/project/customeraccesses/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_project_getCustomerAccessByProject;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_getCustomerAccessByProject')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::getCustomerAccessByProjectAction',  '_format' => 'json',));
                        }
                        not_mongo_project_getCustomerAccessByProject:

                        // mongo_project_delCustomerAccess
                        if (preg_match('#^/mongo/project/customeraccess/(?P<projectId>\\d+)/(?P<customerAccessId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_project_delCustomerAccess;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_delCustomerAccess')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::delCustomerAccessAction',  '_format' => 'json',));
                        }
                        not_mongo_project_delCustomerAccess:

                    }

                    if (0 === strpos($pathinfo, '/mongo/project/user')) {
                        // mongo_project_addUserToProject
                        if ($pathinfo === '/mongo/project/user') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_mongo_project_addUserToProject;
                            }

                            return array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::addUserToProjectAction',  '_format' => 'json',  '_route' => 'mongo_project_addUserToProject',);
                        }
                        not_mongo_project_addUserToProject:

                        // mongo_project_removeUserConnected
                        if (0 === strpos($pathinfo, '/mongo/project/userconnected') && preg_match('#^/mongo/project/userconnected/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_project_removeUserConnected;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_removeUserConnected')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::removeUserConnectedAction',  '_format' => 'json',));
                        }
                        not_mongo_project_removeUserConnected:

                        // mongo_project_removeUserToProject
                        if (preg_match('#^/mongo/project/user/(?P<projectId>\\d+)/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_project_removeUserToProject;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_removeUserToProject')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::removeUserToProjectAction',  '_format' => 'json',));
                        }
                        not_mongo_project_removeUserToProject:

                        // mongo_project_getUserToProject
                        if (0 === strpos($pathinfo, '/mongo/project/users') && preg_match('#^/mongo/project/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_project_getUserToProject;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_getUserToProject')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::getUserToProjectAction',  '_format' => 'json',));
                        }
                        not_mongo_project_getUserToProject:

                    }

                    if (0 === strpos($pathinfo, '/mongo/project/color')) {
                        // mongo_project_changeProjectColor
                        if (preg_match('#^/mongo/project/color/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_mongo_project_changeProjectColor;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_changeProjectColor')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::changeProjectColorAction',  '_format' => 'json',));
                        }
                        not_mongo_project_changeProjectColor:

                        // mongo_project_resetProjectColor
                        if (preg_match('#^/mongo/project/color/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_project_resetProjectColor;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_resetProjectColor')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::resetProjectColorAction',  '_format' => 'json',));
                        }
                        not_mongo_project_resetProjectColor:

                    }

                    // mongo_project_getProjectLogo
                    if (0 === strpos($pathinfo, '/mongo/project/logo') && preg_match('#^/mongo/project/logo/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_project_getProjectLogo;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_project_getProjectLogo')), array (  '_controller' => 'MongoBundle\\Controller\\ProjectController::getProjectLogoAction',  '_format' => 'json',));
                    }
                    not_mongo_project_getProjectLogo:

                }

            }

            if (0 === strpos($pathinfo, '/mongo/role')) {
                // mongo_role_addProjectRoles
                if ($pathinfo === '/mongo/role') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_role_addProjectRoles;
                    }

                    return array (  '_controller' => 'MongoBundle:Role:addProjectRoles',  '_format' => 'json',  '_route' => 'mongo_role_addProjectRoles',);
                }
                not_mongo_role_addProjectRoles:

                // mongo_role_delProjectRoles
                if (preg_match('#^/mongo/role/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_role_delProjectRoles;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_delProjectRoles')), array (  '_controller' => 'MongoBundle:Role:delProjectRoles',  '_format' => 'json',));
                }
                not_mongo_role_delProjectRoles:

                // mongo_role_putProjectRoles
                if (preg_match('#^/mongo/role/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_role_putProjectRoles;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_putProjectRoles')), array (  '_controller' => 'MongoBundle:Role:updateProjectRoles',  '_format' => 'json',));
                }
                not_mongo_role_putProjectRoles:

                // mongo_role_getProjectRoles
                if (0 === strpos($pathinfo, '/mongo/roles') && preg_match('#^/mongo/roles/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_role_getProjectRoles;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_getProjectRoles')), array (  '_controller' => 'MongoBundle:Role:getProjectRoles',  '_format' => 'json',));
                }
                not_mongo_role_getProjectRoles:

                if (0 === strpos($pathinfo, '/mongo/role/user')) {
                    // mongo_role_assignPersonToRole
                    if ($pathinfo === '/mongo/role/user') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_role_assignPersonToRole;
                        }

                        return array (  '_controller' => 'MongoBundle:Role:assignPersonToRole',  '_format' => 'json',  '_route' => 'mongo_role_assignPersonToRole',);
                    }
                    not_mongo_role_assignPersonToRole:

                    // mongo_role_updatePersonRole
                    if (preg_match('#^/mongo/role/user/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_role_updatePersonRole;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_updatePersonRole')), array (  '_controller' => 'MongoBundle:Role:updatePersonRole',  '_format' => 'json',));
                    }
                    not_mongo_role_updatePersonRole:

                }

                // mongo_role_getUserRoles
                if ($pathinfo === '/mongo/roles/user') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_role_getUserRoles;
                    }

                    return array (  '_controller' => 'MongoBundle:Role:getUserRoles',  '_format' => 'json',  '_route' => 'mongo_role_getUserRoles',);
                }
                not_mongo_role_getUserRoles:

                // mongo_role_delPersonRole
                if (0 === strpos($pathinfo, '/mongo/role/user') && preg_match('#^/mongo/role/user/(?P<projectId>\\d+)/(?P<userId>\\d+)/(?P<roleId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_role_delPersonRole;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_delPersonRole')), array (  '_controller' => 'MongoBundle:Role:delPersonRole',  '_format' => 'json',));
                }
                not_mongo_role_delPersonRole:

                // mongo_role_getRoleByProjectAndUser
                if (0 === strpos($pathinfo, '/mongo/roles/project/user') && preg_match('#^/mongo/roles/project/user/(?P<projectId>\\d+)(?:/(?P<userId>\\d+))?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_role_getRoleByProjectAndUser;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_getRoleByProjectAndUser')), array (  '_controller' => 'MongoBundle:Role:getRoleByProjectAndUser',  'userId' => 0,  '_format' => 'json',));
                }
                not_mongo_role_getRoleByProjectAndUser:

                if (0 === strpos($pathinfo, '/mongo/role/user')) {
                    // mongo_role_getUsersForRole
                    if (0 === strpos($pathinfo, '/mongo/role/users') && preg_match('#^/mongo/role/users/(?P<roleId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_role_getUsersForRole;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_getUsersForRole')), array (  '_controller' => 'MongoBundle:Role:getUsersForRole',  '_format' => 'json',));
                    }
                    not_mongo_role_getUsersForRole:

                    // mongo_role_getUserRoleForPart
                    if (0 === strpos($pathinfo, '/mongo/role/user/part') && preg_match('#^/mongo/role/user/part/(?P<userId>\\d+)/(?P<projectId>\\d+)/(?P<part>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_role_getUserRoleForPart;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_role_getUserRoleForPart')), array (  '_controller' => 'MongoBundle:Role:getUserRoleForPart',  '_format' => 'json',));
                    }
                    not_mongo_role_getUserRoleForPart:

                }

            }

            if (0 === strpos($pathinfo, '/mongo/statistic')) {
                // mongo_stat_getAllStat
                if (0 === strpos($pathinfo, '/mongo/statistics') && preg_match('#^/mongo/statistics/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_stat_getAllStat;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_stat_getAllStat')), array (  '_controller' => 'MongoBundle\\Controller\\StatisticController::getAllStatAction',  '_format' => 'json',));
                }
                not_mongo_stat_getAllStat:

                // mongo_stat_getStat
                if (preg_match('#^/mongo/statistic/(?P<projectId>\\d+)/(?P<statName>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_stat_getStat;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_stat_getStat')), array (  '_controller' => 'MongoBundle\\Controller\\StatisticController::getStatAction',  '_format' => 'json',));
                }
                not_mongo_stat_getStat:

                if (0 === strpos($pathinfo, '/mongo/statistics/update')) {
                    // mongo_stat_weeklyUpdate
                    if ($pathinfo === '/mongo/statistics/update/weekly') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_stat_weeklyUpdate;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\StatisticController::weeklyUpdateAction',  '_route' => 'mongo_stat_weeklyUpdate',);
                    }
                    not_mongo_stat_weeklyUpdate:

                    // mongo_stat_dailyUpdate
                    if ($pathinfo === '/mongo/statistics/update/daily') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_stat_dailyUpdate;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\StatisticController::dailyUpdateAction',  '_route' => 'mongo_stat_dailyUpdate',);
                    }
                    not_mongo_stat_dailyUpdate:

                }

            }

            if (0 === strpos($pathinfo, '/mongo/t')) {
                if (0 === strpos($pathinfo, '/mongo/task')) {
                    // mongo_task_taskCreation
                    if ($pathinfo === '/mongo/task') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_task_taskCreation;
                        }

                        return array (  '_controller' => 'MongoBundle\\Controller\\TaskController::createTaskAction',  '_format' => 'json',  '_route' => 'mongo_task_taskCreation',);
                    }
                    not_mongo_task_taskCreation:

                    // mongo_task_taskUpdate
                    if (preg_match('#^/mongo/task/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_task_taskUpdate;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_taskUpdate')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::updateTaskAction',  '_format' => 'json',));
                    }
                    not_mongo_task_taskUpdate:

                    // mongo_task_getTaskInfos
                    if (preg_match('#^/mongo/task/(?P<taskId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_task_getTaskInfos;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_getTaskInfos')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::getTaskInfosAction',  '_format' => 'json',));
                    }
                    not_mongo_task_getTaskInfos:

                    // mongo_task_archiveTask
                    if (0 === strpos($pathinfo, '/mongo/task/archive') && preg_match('#^/mongo/task/archive/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_task_archiveTask;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_archiveTask')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::archiveTaskAction',  '_format' => 'json',));
                    }
                    not_mongo_task_archiveTask:

                    // mongo_task_taskDelete
                    if (preg_match('#^/mongo/task/(?P<taskId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_mongo_task_taskDelete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_taskDelete')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::deleteTaskAction',  '_format' => 'json',));
                    }
                    not_mongo_task_taskDelete:

                    if (0 === strpos($pathinfo, '/mongo/tasks')) {
                        if (0 === strpos($pathinfo, '/mongo/tasks/tag')) {
                            // mongo_task_tagCreation
                            if ($pathinfo === '/mongo/tasks/tag') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_mongo_task_tagCreation;
                                }

                                return array (  '_controller' => 'MongoBundle\\Controller\\TaskController::tagCreationAction',  '_format' => 'json',  '_route' => 'mongo_task_tagCreation',);
                            }
                            not_mongo_task_tagCreation:

                            // mongo_task_tagUpdate
                            if (preg_match('#^/mongo/tasks/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                                if ($this->context->getMethod() != 'PUT') {
                                    $allow[] = 'PUT';
                                    goto not_mongo_task_tagUpdate;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_tagUpdate')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::tagUpdateAction',  '_format' => 'json',));
                            }
                            not_mongo_task_tagUpdate:

                            // mongo_task_getTagInfos
                            if (preg_match('#^/mongo/tasks/tag/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_mongo_task_getTagInfos;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_getTagInfos')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::getTagInfosAction',  '_format' => 'json',));
                            }
                            not_mongo_task_getTagInfos:

                            // mongo_task_deleteTag
                            if (preg_match('#^/mongo/tasks/tag/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                                if ($this->context->getMethod() != 'DELETE') {
                                    $allow[] = 'DELETE';
                                    goto not_mongo_task_deleteTag;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_deleteTag')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::deleteTagAction',  '_format' => 'json',));
                            }
                            not_mongo_task_deleteTag:

                        }

                        // mongo_task_getProjectTasks
                        if (0 === strpos($pathinfo, '/mongo/tasks/project') && preg_match('#^/mongo/tasks/project/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_task_getProjectTasks;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_getProjectTasks')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::getProjectTasksAction',  '_format' => 'json',));
                        }
                        not_mongo_task_getProjectTasks:

                        // mongo_task_getProjectTags
                        if (0 === strpos($pathinfo, '/mongo/tasks/tags/project') && preg_match('#^/mongo/tasks/tags/project/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_task_getProjectTags;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_task_getProjectTags')), array (  '_controller' => 'MongoBundle\\Controller\\TaskController::getProjectTagsAction',  '_format' => 'json',));
                        }
                        not_mongo_task_getProjectTags:

                    }

                }

                if (0 === strpos($pathinfo, '/mongo/timeline')) {
                    // mongo_timeline_getTimelines
                    if (0 === strpos($pathinfo, '/mongo/timelines') && preg_match('#^/mongo/timelines/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_timeline_getTimelines;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_getTimelines')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::getTimelinesAction',  '_format' => 'json',));
                    }
                    not_mongo_timeline_getTimelines:

                    if (0 === strpos($pathinfo, '/mongo/timeline/message')) {
                        // mongo_timeline_postMessage
                        if (preg_match('#^/mongo/timeline/message/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_mongo_timeline_postMessage;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_postMessage')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::postMessageAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_postMessage:

                        // mongo_timeline_editMessage
                        if (preg_match('#^/mongo/timeline/message/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_mongo_timeline_editMessage;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_editMessage')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::editMessageAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_editMessage:

                        // mongo_timeline_archiveMessage
                        if (preg_match('#^/mongo/timeline/message/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_timeline_archiveMessage;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_archiveMessage')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::archiveMessageAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_archiveMessage:

                        if (0 === strpos($pathinfo, '/mongo/timeline/messages')) {
                            // mongo_timeline_getMessages
                            if (preg_match('#^/mongo/timeline/messages/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_mongo_timeline_getMessages;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_getMessages')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::getMessagesAction',  '_format' => 'json',));
                            }
                            not_mongo_timeline_getMessages:

                            // mongo_timeline_getLastMessages
                            if (preg_match('#^/mongo/timeline/messages/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_mongo_timeline_getLastMessages;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_getLastMessages')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::getLastMessagesAction',  '_format' => 'json',));
                            }
                            not_mongo_timeline_getLastMessages:

                        }

                        // mongo_timeline_getComments
                        if (0 === strpos($pathinfo, '/mongo/timeline/message/comments') && preg_match('#^/mongo/timeline/message/comments/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_mongo_timeline_getComments;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_getComments')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::getCommentsAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_getComments:

                    }

                    if (0 === strpos($pathinfo, '/mongo/timeline/comment')) {
                        // mongo_timeline_postComment
                        if (preg_match('#^/mongo/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_mongo_timeline_postComment;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_postComment')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::postCommentAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_postComment:

                        // mongo_timeline_editComment
                        if (preg_match('#^/mongo/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_mongo_timeline_editComment;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_editComment')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::editCommentAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_editComment:

                        // mongo_timeline_deleteComment
                        if (preg_match('#^/mongo/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_mongo_timeline_deleteComment;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_timeline_deleteComment')), array (  '_controller' => 'MongoBundle\\Controller\\TimelineController::deleteCommentAction',  '_format' => 'json',));
                        }
                        not_mongo_timeline_deleteComment:

                    }

                }

            }

            if (0 === strpos($pathinfo, '/mongo/user')) {
                // mongo_user_basicInformations
                if ($pathinfo === '/mongo/user') {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_mongo_user_basicInformations;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\UserController::basicInformationsAction',  '_format' => 'json',  '_route' => 'mongo_user_basicInformations',);
                }
                not_mongo_user_basicInformations:

                // mongo_user_getUserBasicInformations
                if (preg_match('#^/mongo/user/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_user_getUserBasicInformations;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_getUserBasicInformations')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::getUserBasicInformationsAction',  '_format' => 'json',));
                }
                not_mongo_user_getUserBasicInformations:

                if (0 === strpos($pathinfo, '/mongo/user/id')) {
                    // mongo_user_getIdByName
                    if (preg_match('#^/mongo/user/id/(?P<firstname>[^/]++)/(?P<lastname>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_user_getIdByName;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_getIdByName')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::getIdByNameAction',  '_format' => 'json',));
                    }
                    not_mongo_user_getIdByName:

                    // mongo_user_getIdByEmail
                    if (preg_match('#^/mongo/user/id/(?P<email>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_mongo_user_getIdByEmail;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_getIdByEmail')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::getIdByEmailAction',  '_format' => 'json',));
                    }
                    not_mongo_user_getIdByEmail:

                }

                // mongo_user_getUserAvatar
                if (0 === strpos($pathinfo, '/mongo/user/avatar') && preg_match('#^/mongo/user/avatar/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_user_getUserAvatar;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_getUserAvatar')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::getUserAvatarAction',  '_format' => 'json',));
                }
                not_mongo_user_getUserAvatar:

                // mongo_user_getAllProjectUserAvatar
                if (0 === strpos($pathinfo, '/mongo/user/project/avatars') && preg_match('#^/mongo/user/project/avatars/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_user_getAllProjectUserAvatar;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_user_getAllProjectUserAvatar')), array (  '_controller' => 'MongoBundle\\Controller\\UserController::getAllProjectUserAvatarAction',  '_format' => 'json',));
                }
                not_mongo_user_getAllProjectUserAvatar:

            }

            if (0 === strpos($pathinfo, '/mongo/whiteboard')) {
                // mongo_whiteboard_list
                if (0 === strpos($pathinfo, '/mongo/whiteboards') && preg_match('#^/mongo/whiteboards/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_whiteboard_list;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_list')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::listWhiteboardAction',  '_format' => 'json',));
                }
                not_mongo_whiteboard_list:

                // mongo_whiteboard_new
                if ($pathinfo === '/mongo/whiteboard') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_mongo_whiteboard_new;
                    }

                    return array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::newWhiteboardAction',  '_format' => 'json',  '_route' => 'mongo_whiteboard_new',);
                }
                not_mongo_whiteboard_new:

                // mongo_whiteboard_open
                if (preg_match('#^/mongo/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_mongo_whiteboard_open;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_open')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::openWhiteboardAction',  '_format' => 'json',));
                }
                not_mongo_whiteboard_open:

                // mongo_whiteboard_close
                if (preg_match('#^/mongo/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_mongo_whiteboard_close;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_close')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::closeWhiteboardAction',  '_format' => 'json',));
                }
                not_mongo_whiteboard_close:

                if (0 === strpos($pathinfo, '/mongo/whiteboard/draw')) {
                    // mongo_whiteboard_pushDraw
                    if (preg_match('#^/mongo/whiteboard/draw/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_mongo_whiteboard_pushDraw;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_pushDraw')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::pushDrawAction',  '_format' => 'json',));
                    }
                    not_mongo_whiteboard_pushDraw:

                    // mongo_whiteboard_pullDraw
                    if (preg_match('#^/mongo/whiteboard/draw/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_mongo_whiteboard_pullDraw;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_pullDraw')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::pullDrawAction',  '_format' => 'json',));
                    }
                    not_mongo_whiteboard_pullDraw:

                }

                // mongo_whiteboard_delete
                if (preg_match('#^/mongo/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_whiteboard_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_delete')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::delWhiteboardAction',  '_format' => 'json',));
                }
                not_mongo_whiteboard_delete:

                // mongo_whiteboard_deleteObject
                if (0 === strpos($pathinfo, '/mongo/whiteboard/object') && preg_match('#^/mongo/whiteboard/object/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_mongo_whiteboard_deleteObject;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'mongo_whiteboard_deleteObject')), array (  '_controller' => 'MongoBundle\\Controller\\WhiteboardController::deleteObjectAction',  '_format' => 'json',));
                }
                not_mongo_whiteboard_deleteObject:

            }

        }

        // user_passwordEncrypt
        if (0 === strpos($pathinfo, '/user/passwordencrypt') && preg_match('#^/user/passwordencrypt/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_user_passwordEncrypt;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_passwordEncrypt')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::passwordEncryptAction',  '_format' => 'json',));
        }
        not_user_passwordEncrypt:

        if (0 === strpos($pathinfo, '/account')) {
            // accountAdministration_preorder
            if ($pathinfo === '/account/preorder') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_accountAdministration_preorder;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\AccountAdministrationController::preorderAction',  '_format' => 'json',  '_route' => 'accountAdministration_preorder',);
            }
            not_accountAdministration_preorder:

            if (0 === strpos($pathinfo, '/account/log')) {
                // accountAdministration_login
                if ($pathinfo === '/account/login') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_accountAdministration_login;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\AccountAdministrationController::loginAction',  '_format' => 'json',  '_route' => 'accountAdministration_login',);
                }
                not_accountAdministration_login:

                // accountAdministration_logout
                if ($pathinfo === '/account/logout') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_accountAdministration_logout;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\AccountAdministrationController::logoutAction',  '_format' => 'json',  '_route' => 'accountAdministration_logout',);
                }
                not_accountAdministration_logout:

            }

            // accountAdministration_register
            if ($pathinfo === '/account/register') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_accountAdministration_register;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\AccountAdministrationController::registerAction',  '_format' => 'json',  '_route' => 'accountAdministration_register',);
            }
            not_accountAdministration_register:

        }

        if (0 === strpos($pathinfo, '/bugtracker')) {
            if (0 === strpos($pathinfo, '/bugtracker/ticket')) {
                // bugtracker_postTicket
                if ($pathinfo === '/bugtracker/ticket') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_bugtracker_postTicket;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::postTicketAction',  '_format' => 'json',  '_route' => 'bugtracker_postTicket',);
                }
                not_bugtracker_postTicket:

                // bugtracker_editTicket
                if (preg_match('#^/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_bugtracker_editTicket;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_editTicket')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::editTicketAction',  '_format' => 'json',));
                }
                not_bugtracker_editTicket:

                // bugtracker_closeTicket
                if (0 === strpos($pathinfo, '/bugtracker/ticket/close') && preg_match('#^/bugtracker/ticket/close/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_bugtracker_closeTicket;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_closeTicket')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::closeTicketAction',  '_format' => 'json',));
                }
                not_bugtracker_closeTicket:

                // bugtracker_reopenTicket
                if (0 === strpos($pathinfo, '/bugtracker/ticket/reopen') && preg_match('#^/bugtracker/ticket/reopen/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_reopenTicket;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_reopenTicket')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::reopenTicketAction',  '_format' => 'json',));
                }
                not_bugtracker_reopenTicket:

                // bugtracker_deleteTicket
                if (preg_match('#^/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_bugtracker_deleteTicket;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_deleteTicket')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::deleteTicketAction',  '_format' => 'json',));
                }
                not_bugtracker_deleteTicket:

                // bugtracker_getTicket
                if (preg_match('#^/bugtracker/ticket/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_getTicket;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getTicket')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getTicketAction',  '_format' => 'json',));
                }
                not_bugtracker_getTicket:

                if (0 === strpos($pathinfo, '/bugtracker/tickets')) {
                    // bugtracker_getTickets
                    if (0 === strpos($pathinfo, '/bugtracker/tickets/opened') && preg_match('#^/bugtracker/tickets/opened/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_bugtracker_getTickets;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getTickets')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getTicketsAction',  '_format' => 'json',));
                    }
                    not_bugtracker_getTickets:

                    // bugtracker_getClosedTickets
                    if (0 === strpos($pathinfo, '/bugtracker/tickets/closed') && preg_match('#^/bugtracker/tickets/closed/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_bugtracker_getClosedTickets;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getClosedTickets')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getClosedTicketsAction',  '_format' => 'json',));
                    }
                    not_bugtracker_getClosedTickets:

                    // bugtracker_getLastTickets
                    if (0 === strpos($pathinfo, '/bugtracker/tickets/opened') && preg_match('#^/bugtracker/tickets/opened/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_bugtracker_getLastTickets;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getLastTickets')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getLastTicketsAction',  '_format' => 'json',));
                    }
                    not_bugtracker_getLastTickets:

                    // bugtracker_getLastClosedTickets
                    if (0 === strpos($pathinfo, '/bugtracker/tickets/closed') && preg_match('#^/bugtracker/tickets/closed/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_bugtracker_getLastClosedTickets;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getLastClosedTickets')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getLastClosedTicketsAction',  '_format' => 'json',));
                    }
                    not_bugtracker_getLastClosedTickets:

                    // bugtracker_getTicketsByUser
                    if (0 === strpos($pathinfo, '/bugtracker/tickets/user') && preg_match('#^/bugtracker/tickets/user/(?P<id>\\d+)/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_bugtracker_getTicketsByUser;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getTicketsByUser')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getTicketsByUserAction',  '_format' => 'json',));
                    }
                    not_bugtracker_getTicketsByUser:

                }

            }

            // bugtracker_setParticipants
            if (0 === strpos($pathinfo, '/bugtracker/users') && preg_match('#^/bugtracker/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_bugtracker_setParticipants;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_setParticipants')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::setParticipantsAction',  '_format' => 'json',));
            }
            not_bugtracker_setParticipants:

            if (0 === strpos($pathinfo, '/bugtracker/comment')) {
                // bugtracker_getComments
                if (0 === strpos($pathinfo, '/bugtracker/comments') && preg_match('#^/bugtracker/comments/(?P<ticketId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_getComments;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getComments')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getCommentsAction',  '_format' => 'json',));
                }
                not_bugtracker_getComments:

                // bugtracker_postComment
                if ($pathinfo === '/bugtracker/comment') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_bugtracker_postComment;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::postCommentAction',  '_format' => 'json',  '_route' => 'bugtracker_postComment',);
                }
                not_bugtracker_postComment:

                // bugtracker_editComment
                if (preg_match('#^/bugtracker/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_bugtracker_editComment;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_editComment')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::editCommentAction',  '_format' => 'json',));
                }
                not_bugtracker_editComment:

                // bugtracker_deleteComment
                if (preg_match('#^/bugtracker/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_bugtracker_deleteComment;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_deleteComment')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::deleteCommentAction',  '_format' => 'json',));
                }
                not_bugtracker_deleteComment:

            }

            if (0 === strpos($pathinfo, '/bugtracker/tag')) {
                // bugtracker_tagCreation
                if ($pathinfo === '/bugtracker/tag') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_bugtracker_tagCreation;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::tagCreationAction',  '_format' => 'json',  '_route' => 'bugtracker_tagCreation',);
                }
                not_bugtracker_tagCreation:

                // bugtracker_tagUpdate
                if (preg_match('#^/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_bugtracker_tagUpdate;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_tagUpdate')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::tagUpdateAction',  '_format' => 'json',));
                }
                not_bugtracker_tagUpdate:

                // bugtracker_getTagInfos
                if (preg_match('#^/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_getTagInfos;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getTagInfos')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getTagInfosAction',  '_format' => 'json',));
                }
                not_bugtracker_getTagInfos:

                // bugtracker_deleteTag
                if (preg_match('#^/bugtracker/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_bugtracker_deleteTag;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_deleteTag')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::deleteTagAction',  '_format' => 'json',));
                }
                not_bugtracker_deleteTag:

                // bugtracker_assignTag
                if (0 === strpos($pathinfo, '/bugtracker/tag/assign') && preg_match('#^/bugtracker/tag/assign/(?P<bugId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_bugtracker_assignTag;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_assignTag')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::assignTagAction',  '_format' => 'json',));
                }
                not_bugtracker_assignTag:

                // bugtracker_removeTag
                if (0 === strpos($pathinfo, '/bugtracker/tag/remove') && preg_match('#^/bugtracker/tag/remove/(?P<bugId>\\d+)/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_bugtracker_removeTag;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_removeTag')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::removeTagAction',  '_format' => 'json',));
                }
                not_bugtracker_removeTag:

            }

            // bugtracker_getProjectTags
            if (0 === strpos($pathinfo, '/bugtracker/project/tags') && preg_match('#^/bugtracker/project/tags/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_bugtracker_getProjectTags;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getProjectTags')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getProjectTagsAction',  '_format' => 'json',));
            }
            not_bugtracker_getProjectTags:

            if (0 === strpos($pathinfo, '/bugtracker/get')) {
                // bugtracker_getTicketsByState
                if (0 === strpos($pathinfo, '/bugtracker/getticketsbystate') && preg_match('#^/bugtracker/getticketsbystate/(?P<id>\\d+)/(?P<state>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_getTicketsByState;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'bugtracker_getTicketsByState')), array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getTicketsByStateAction',  '_format' => 'json',));
                }
                not_bugtracker_getTicketsByState:

                // bugtracker_getStates
                if ($pathinfo === '/bugtracker/getstates') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_bugtracker_getStates;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\BugtrackerController::getStatesAction',  '_format' => 'json',  '_route' => 'bugtracker_getStates',);
                }
                not_bugtracker_getStates:

            }

        }

        if (0 === strpos($pathinfo, '/cloud')) {
            if (0 === strpos($pathinfo, '/cloud/stream')) {
                // cloud_streamOpenAction
                if (preg_match('#^/cloud/stream/(?P<idProject>[^/]++)(?:/(?P<safePassword>[^/]++))?$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_cloud_streamOpenAction;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_streamOpenAction')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::openStreamAction',  'safePassword' => NULL,  '_format' => 'json',));
                }
                not_cloud_streamOpenAction:

                // cloud_streamCloseAction
                if (preg_match('#^/cloud/stream/(?P<projectId>[^/]++)/(?P<streamId>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_cloud_streamCloseAction;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_streamCloseAction')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::closeStreamAction',  '_format' => 'json',));
                }
                not_cloud_streamCloseAction:

            }

            // cloud_sendFile
            if ($pathinfo === '/cloud/file') {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_cloud_sendFile;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\CloudController::sendFileAction',  '_format' => 'json',  '_route' => 'cloud_sendFile',);
            }
            not_cloud_sendFile:

            // cloud_getList
            if (0 === strpos($pathinfo, '/cloud/list') && preg_match('#^/cloud/list/(?P<idProject>[^/]++)/(?P<path>[^/]++)(?:/(?P<password>[^/]++))?$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cloud_getList;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_getList')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::getListAction',  'password' => NULL,  '_format' => 'json',));
            }
            not_cloud_getList:

            if (0 === strpos($pathinfo, '/cloud/file')) {
                // cloud_getFile
                if (preg_match('#^/cloud/file/(?P<cloudPath>[^/]++)/(?P<idProject>[^/]++)(?:/(?P<passwordSafe>[^/]++))?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_cloud_getFile;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_getFile')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::getFileAction',  'passwordSafe' => NULL,));
                }
                not_cloud_getFile:

                // cloud_getFile_secured
                if (0 === strpos($pathinfo, '/cloud/filesecured') && preg_match('#^/cloud/filesecured/(?P<cloudPath>[^/]++)/(?P<idProject>[^/]++)/(?P<password>[^/]++)(?:/(?P<passwordSafe>[^/]++))?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_cloud_getFile_secured;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_getFile_secured')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::getFileSecuredAction',  'passwordSafe' => NULL,));
                }
                not_cloud_getFile_secured:

            }

            // cloud_setSafePass
            if ($pathinfo === '/cloud/safepass') {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_cloud_setSafePass;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\CloudController::setSafePassAction',  '_route' => 'cloud_setSafePass',);
            }
            not_cloud_setSafePass:

            // cloud_delete
            if (0 === strpos($pathinfo, '/cloud/file') && preg_match('#^/cloud/file/(?P<projectId>[^/]++)/(?P<path>[^/]++)(?:/(?P<password>[^/]++))?$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_cloud_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_delete')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::delAction',  'password' => NULL,  '_format' => 'json',));
            }
            not_cloud_delete:

            // cloud_createDir
            if ($pathinfo === '/cloud/createdir') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_cloud_createDir;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\CloudController::createDirAction',  '_format' => 'json',  '_route' => 'cloud_createDir',);
            }
            not_cloud_createDir:

            // cloud_delete_secured
            if (0 === strpos($pathinfo, '/cloud/filesecured') && preg_match('#^/cloud/filesecured/(?P<projectId>[^/]++)/(?P<path>[^/]++)/(?P<password>[^/]++)(?:/(?P<safe_password>[^/]++))?$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_cloud_delete_secured;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cloud_delete_secured')), array (  '_controller' => 'SQLBundle\\Controller\\CloudController::delSecuredAction',  'safe_password' => NULL,  '_format' => 'json',));
            }
            not_cloud_delete_secured:

        }

        if (0 === strpos($pathinfo, '/dashboard')) {
            // dashboard_getTeamOccupation
            if (0 === strpos($pathinfo, '/dashboard/occupation') && preg_match('#^/dashboard/occupation/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_dashboard_getTeamOccupation;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'dashboard_getTeamOccupation')), array (  '_controller' => 'SQLBundle\\Controller\\DashboardController::getTeamOccupationAction',  '_format' => 'json',));
            }
            not_dashboard_getTeamOccupation:

            // dashboard_getNextMeetings
            if (0 === strpos($pathinfo, '/dashboard/meetings') && preg_match('#^/dashboard/meetings/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_dashboard_getNextMeetings;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'dashboard_getNextMeetings')), array (  '_controller' => 'SQLBundle\\Controller\\DashboardController::getNextMeetingsAction',  '_format' => 'json',));
            }
            not_dashboard_getNextMeetings:

            // dashboard_getProjectsGlobalProgress
            if ($pathinfo === '/dashboard/projects') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_dashboard_getProjectsGlobalProgress;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\DashboardController::getProjectsGlobalProgressAction',  '_format' => 'json',  '_route' => 'dashboard_getProjectsGlobalProgress',);
            }
            not_dashboard_getProjectsGlobalProgress:

        }

        if (0 === strpos($pathinfo, '/event')) {
            // event_postEvent
            if ($pathinfo === '/event') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_event_postEvent;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\EventController::postEventAction',  '_format' => 'json',  '_route' => 'event_postEvent',);
            }
            not_event_postEvent:

            // event_editEvent
            if (preg_match('#^/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_event_editEvent;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'event_editEvent')), array (  '_controller' => 'SQLBundle\\Controller\\EventController::editEventAction',  '_format' => 'json',));
            }
            not_event_editEvent:

            // event_delEvent
            if (preg_match('#^/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_event_delEvent;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'event_delEvent')), array (  '_controller' => 'SQLBundle\\Controller\\EventController::delEventAction',  '_format' => 'json',));
            }
            not_event_delEvent:

            // event_getEvent
            if (preg_match('#^/event/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_event_getEvent;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'event_getEvent')), array (  '_controller' => 'SQLBundle\\Controller\\EventController::getEventAction',  '_format' => 'json',));
            }
            not_event_getEvent:

            // event_setParticipants
            if (0 === strpos($pathinfo, '/event/users') && preg_match('#^/event/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_event_setParticipants;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'event_setParticipants')), array (  '_controller' => 'SQLBundle\\Controller\\EventController::setParticipantsAction',  '_format' => 'json',));
            }
            not_event_setParticipants:

        }

        if (0 === strpos($pathinfo, '/notification')) {
            if (0 === strpos($pathinfo, '/notification/device')) {
                // notification_registerDevice
                if ($pathinfo === '/notification/device') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_notification_registerDevice;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\NotificationController::registerDeviceAction',  '_format' => 'json',  '_route' => 'notification_registerDevice',);
                }
                not_notification_registerDevice:

                // notification_getUserDevices
                if ($pathinfo === '/notification/devices') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_notification_getUserDevices;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\NotificationController::getUserDevicesAction',  '_format' => 'json',  '_route' => 'notification_getUserDevices',);
                }
                not_notification_getUserDevices:

            }

            // notification_getNotifications
            if (preg_match('#^/notification/(?P<read>[a-zA-Z0-9]+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_notification_getNotifications;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'notification_getNotifications')), array (  '_controller' => 'SQLBundle\\Controller\\NotificationController::getNotificationsAction',  '_format' => 'json',));
            }
            not_notification_getNotifications:

            // notification_setNotificationToRead
            if (0 === strpos($pathinfo, '/notification/setnotificationtoread') && preg_match('#^/notification/setnotificationtoread/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_notification_setNotificationToRead;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'notification_setNotificationToRead')), array (  '_controller' => 'SQLBundle\\Controller\\NotificationController::setNotificationToReadAction',  '_format' => 'json',));
            }
            not_notification_setNotificationToRead:

        }

        if (0 === strpos($pathinfo, '/p')) {
            if (0 === strpos($pathinfo, '/planning')) {
                // planning_getDayPlanning
                if (0 === strpos($pathinfo, '/planning/day') && preg_match('#^/planning/day/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_planning_getDayPlanning;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'planning_getDayPlanning')), array (  '_controller' => 'SQLBundle\\Controller\\PlanningController::getDayPlanningAction',  '_format' => 'json',));
                }
                not_planning_getDayPlanning:

                // planning_getWeekPlanning
                if (0 === strpos($pathinfo, '/planning/week') && preg_match('#^/planning/week/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_planning_getWeekPlanning;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'planning_getWeekPlanning')), array (  '_controller' => 'SQLBundle\\Controller\\PlanningController::getWeekPlanningAction',  '_format' => 'json',));
                }
                not_planning_getWeekPlanning:

                // planning_getMonthPlanning
                if (0 === strpos($pathinfo, '/planning/month') && preg_match('#^/planning/month/(?P<date>\\d+-\\d+-\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_planning_getMonthPlanning;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'planning_getMonthPlanning')), array (  '_controller' => 'SQLBundle\\Controller\\PlanningController::getMonthPlanningAction',  '_format' => 'json',));
                }
                not_planning_getMonthPlanning:

            }

            if (0 === strpos($pathinfo, '/project')) {
                // project_projectCreation
                if ($pathinfo === '/project') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_project_projectCreation;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::projectCreationAction',  '_format' => 'json',  '_route' => 'project_projectCreation',);
                }
                not_project_projectCreation:

                // project_updateInformations
                if (preg_match('#^/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_project_updateInformations;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_updateInformations')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::updateInformationsAction',  '_format' => 'json',));
                }
                not_project_updateInformations:

                // project_getInformations
                if (preg_match('#^/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_project_getInformations;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_getInformations')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::getInformationsAction',  '_format' => 'json',));
                }
                not_project_getInformations:

                // project_delProject
                if (preg_match('#^/project/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_project_delProject;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_delProject')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::delProjectAction',  '_format' => 'json',));
                }
                not_project_delProject:

                // project_retrieveProject
                if (0 === strpos($pathinfo, '/project/retrieve') && preg_match('#^/project/retrieve/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_project_retrieveProject;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_retrieveProject')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::retrieveProjectAction',  '_format' => 'json',));
                }
                not_project_retrieveProject:

                if (0 === strpos($pathinfo, '/project/customeraccess')) {
                    // project_generateCustomerAccess
                    if ($pathinfo === '/project/customeraccess') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_project_generateCustomerAccess;
                        }

                        return array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::generateCustomerAccessAction',  '_format' => 'json',  '_route' => 'project_generateCustomerAccess',);
                    }
                    not_project_generateCustomerAccess:

                    // project_getCustomerAccessByProject
                    if (0 === strpos($pathinfo, '/project/customeraccesses') && preg_match('#^/project/customeraccesses/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_project_getCustomerAccessByProject;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_getCustomerAccessByProject')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::getCustomerAccessByProjectAction',  '_format' => 'json',));
                    }
                    not_project_getCustomerAccessByProject:

                    // project_delCustomerAccess
                    if (preg_match('#^/project/customeraccess/(?P<projectId>\\d+)/(?P<customerAccessId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_project_delCustomerAccess;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_delCustomerAccess')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::delCustomerAccessAction',  '_format' => 'json',));
                    }
                    not_project_delCustomerAccess:

                }

                if (0 === strpos($pathinfo, '/project/user')) {
                    // project_addUserToProject
                    if ($pathinfo === '/project/user') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_project_addUserToProject;
                        }

                        return array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::addUserToProjectAction',  '_format' => 'json',  '_route' => 'project_addUserToProject',);
                    }
                    not_project_addUserToProject:

                    // project_removeUserConnected
                    if (0 === strpos($pathinfo, '/project/userconnected') && preg_match('#^/project/userconnected/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_project_removeUserConnected;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_removeUserConnected')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::removeUserConnectedAction',  '_format' => 'json',));
                    }
                    not_project_removeUserConnected:

                    // project_removeUserToProject
                    if (preg_match('#^/project/user/(?P<projectId>\\d+)/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_project_removeUserToProject;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_removeUserToProject')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::removeUserToProjectAction',  '_format' => 'json',));
                    }
                    not_project_removeUserToProject:

                    // project_getUserToProject
                    if (0 === strpos($pathinfo, '/project/users') && preg_match('#^/project/users/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_project_getUserToProject;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_getUserToProject')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::getUserToProjectAction',  '_format' => 'json',));
                    }
                    not_project_getUserToProject:

                }

                if (0 === strpos($pathinfo, '/project/color')) {
                    // project_changeProjectColor
                    if (preg_match('#^/project/color/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_project_changeProjectColor;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_changeProjectColor')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::changeProjectColorAction',  '_format' => 'json',));
                    }
                    not_project_changeProjectColor:

                    // project_resetProjectColor
                    if (preg_match('#^/project/color/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_project_resetProjectColor;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_resetProjectColor')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::resetProjectColorAction',  '_format' => 'json',));
                    }
                    not_project_resetProjectColor:

                }

                // project_getProjectLogo
                if (0 === strpos($pathinfo, '/project/logo') && preg_match('#^/project/logo/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_project_getProjectLogo;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'project_getProjectLogo')), array (  '_controller' => 'SQLBundle\\Controller\\ProjectController::getProjectLogoAction',  '_format' => 'json',));
                }
                not_project_getProjectLogo:

            }

        }

        if (0 === strpos($pathinfo, '/role')) {
            // role_addProjectRoles
            if ($pathinfo === '/role') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_role_addProjectRoles;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\RoleController::addProjectRolesAction',  '_format' => 'json',  '_route' => 'role_addProjectRoles',);
            }
            not_role_addProjectRoles:

            // role_delProjectRoles
            if (preg_match('#^/role/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_role_delProjectRoles;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_delProjectRoles')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::delProjectRolesAction',  '_format' => 'json',));
            }
            not_role_delProjectRoles:

            // role_putProjectRoles
            if (preg_match('#^/role/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_role_putProjectRoles;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_putProjectRoles')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::updateProjectRolesAction',  '_format' => 'json',));
            }
            not_role_putProjectRoles:

            // role_getProjectRoles
            if (0 === strpos($pathinfo, '/roles') && preg_match('#^/roles/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_role_getProjectRoles;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_getProjectRoles')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::getProjectRolesAction',  '_format' => 'json',));
            }
            not_role_getProjectRoles:

            if (0 === strpos($pathinfo, '/role/user')) {
                // role_assignPersonToRole
                if ($pathinfo === '/role/user') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_role_assignPersonToRole;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\RoleController::assignPersonToRoleAction',  '_format' => 'json',  '_route' => 'role_assignPersonToRole',);
                }
                not_role_assignPersonToRole:

                // role_updatePersonRole
                if (preg_match('#^/role/user/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_role_updatePersonRole;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_updatePersonRole')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::updatePersonRoleAction',  '_format' => 'json',));
                }
                not_role_updatePersonRole:

            }

            // role_getUserRoles
            if ($pathinfo === '/roles/user') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_role_getUserRoles;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\RoleController::getUserRolesAction',  '_format' => 'json',  '_route' => 'role_getUserRoles',);
            }
            not_role_getUserRoles:

            // role_delPersonRole
            if (0 === strpos($pathinfo, '/role/user') && preg_match('#^/role/user/(?P<projectId>\\d+)/(?P<userId>\\d+)/(?P<roleId>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_role_delPersonRole;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_delPersonRole')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::delPersonRoleAction',  '_format' => 'json',));
            }
            not_role_delPersonRole:

            // role_getRoleByProjectAndUser
            if (0 === strpos($pathinfo, '/roles/project/user') && preg_match('#^/roles/project/user/(?P<projectId>\\d+)(?:/(?P<userId>\\d+))?$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_role_getRoleByProjectAndUser;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_getRoleByProjectAndUser')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::getRoleByProjectAndUserAction',  'userId' => 0,  '_format' => 'json',));
            }
            not_role_getRoleByProjectAndUser:

            if (0 === strpos($pathinfo, '/role/user')) {
                // role_getUsersForRole
                if (0 === strpos($pathinfo, '/role/users') && preg_match('#^/role/users/(?P<roleId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_role_getUsersForRole;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_getUsersForRole')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::getUsersForRoleAction',  '_format' => 'json',));
                }
                not_role_getUsersForRole:

                // role_getUserRoleForPart
                if (0 === strpos($pathinfo, '/role/user/part') && preg_match('#^/role/user/part/(?P<userId>\\d+)/(?P<projectId>\\d+)/(?P<part>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_role_getUserRoleForPart;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'role_getUserRoleForPart')), array (  '_controller' => 'SQLBundle\\Controller\\RoleController::getUserRoleForPartAction',  '_format' => 'json',));
                }
                not_role_getUserRoleForPart:

            }

        }

        if (0 === strpos($pathinfo, '/statistic')) {
            // stat_getAllStat
            if (0 === strpos($pathinfo, '/statistics') && preg_match('#^/statistics/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_stat_getAllStat;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'stat_getAllStat')), array (  '_controller' => 'SQLBundle\\Controller\\StatisticController::getAllStatAction',  '_format' => 'json',));
            }
            not_stat_getAllStat:

            // stat_getStat
            if (preg_match('#^/statistic/(?P<projectId>\\d+)/(?P<statName>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_stat_getStat;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'stat_getStat')), array (  '_controller' => 'SQLBundle\\Controller\\StatisticController::getStatAction',  '_format' => 'json',));
            }
            not_stat_getStat:

            if (0 === strpos($pathinfo, '/statistics/update')) {
                // stat_weeklyUpdate
                if ($pathinfo === '/statistics/update/weekly') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stat_weeklyUpdate;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\StatisticController::weeklyUpdateAction',  '_route' => 'stat_weeklyUpdate',);
                }
                not_stat_weeklyUpdate:

                // stat_dailyUpdate
                if ($pathinfo === '/statistics/update/daily') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stat_dailyUpdate;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\StatisticController::dailyUpdateAction',  '_route' => 'stat_dailyUpdate',);
                }
                not_stat_dailyUpdate:

                // stat_customUpdate
                if ($pathinfo === '/statistics/update/custom') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_stat_customUpdate;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\StatisticController::manuallyUpdateStatAction',  '_route' => 'stat_customUpdate',);
                }
                not_stat_customUpdate:

            }

        }

        if (0 === strpos($pathinfo, '/t')) {
            if (0 === strpos($pathinfo, '/task')) {
                // task_taskCreation
                if ($pathinfo === '/task') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_task_taskCreation;
                    }

                    return array (  '_controller' => 'SQLBundle\\Controller\\TaskController::createTaskAction',  '_format' => 'json',  '_route' => 'task_taskCreation',);
                }
                not_task_taskCreation:

                // task_taskUpdate
                if (preg_match('#^/task/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_task_taskUpdate;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_taskUpdate')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::updateTaskAction',  '_format' => 'json',));
                }
                not_task_taskUpdate:

                // task_getTaskInfos
                if (preg_match('#^/task/(?P<taskId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_task_getTaskInfos;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_getTaskInfos')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::getTaskInfosAction',  '_format' => 'json',));
                }
                not_task_getTaskInfos:

                // task_archiveTask
                if (0 === strpos($pathinfo, '/task/archive') && preg_match('#^/task/archive/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_task_archiveTask;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_archiveTask')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::archiveTaskAction',  '_format' => 'json',));
                }
                not_task_archiveTask:

                // task_taskDelete
                if (preg_match('#^/task/(?P<taskId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_task_taskDelete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_taskDelete')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::deleteTaskAction',  '_format' => 'json',));
                }
                not_task_taskDelete:

                if (0 === strpos($pathinfo, '/tasks')) {
                    if (0 === strpos($pathinfo, '/tasks/tag')) {
                        // task_tagCreation
                        if ($pathinfo === '/tasks/tag') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_task_tagCreation;
                            }

                            return array (  '_controller' => 'SQLBundle\\Controller\\TaskController::tagCreationAction',  '_format' => 'json',  '_route' => 'task_tagCreation',);
                        }
                        not_task_tagCreation:

                        // task_tagUpdate
                        if (preg_match('#^/tasks/tag/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_task_tagUpdate;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_tagUpdate')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::tagUpdateAction',  '_format' => 'json',));
                        }
                        not_task_tagUpdate:

                        // task_getTagInfos
                        if (preg_match('#^/tasks/tag/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_task_getTagInfos;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_getTagInfos')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::getTagInfosAction',  '_format' => 'json',));
                        }
                        not_task_getTagInfos:

                        // task_deleteTag
                        if (preg_match('#^/tasks/tag/(?P<tagId>\\d+)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'DELETE') {
                                $allow[] = 'DELETE';
                                goto not_task_deleteTag;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_deleteTag')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::deleteTagAction',  '_format' => 'json',));
                        }
                        not_task_deleteTag:

                    }

                    // task_getProjectTasks
                    if (0 === strpos($pathinfo, '/tasks/project') && preg_match('#^/tasks/project/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_task_getProjectTasks;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_getProjectTasks')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::getProjectTasksAction',  '_format' => 'json',));
                    }
                    not_task_getProjectTasks:

                    // task_getProjectTags
                    if (0 === strpos($pathinfo, '/tasks/tags/project') && preg_match('#^/tasks/tags/project/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_task_getProjectTags;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_getProjectTags')), array (  '_controller' => 'SQLBundle\\Controller\\TaskController::getProjectTagsAction',  '_format' => 'json',));
                    }
                    not_task_getProjectTags:

                }

            }

            if (0 === strpos($pathinfo, '/timeline')) {
                // timeline_getTimelines
                if (0 === strpos($pathinfo, '/timelines') && preg_match('#^/timelines/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_timeline_getTimelines;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_getTimelines')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::getTimelinesAction',  '_format' => 'json',));
                }
                not_timeline_getTimelines:

                if (0 === strpos($pathinfo, '/timeline/message')) {
                    // timeline_postMessage
                    if (preg_match('#^/timeline/message/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_timeline_postMessage;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_postMessage')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::postMessageAction',  '_format' => 'json',));
                    }
                    not_timeline_postMessage:

                    // timeline_editMessage
                    if (preg_match('#^/timeline/message/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_timeline_editMessage;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_editMessage')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::editMessageAction',  '_format' => 'json',));
                    }
                    not_timeline_editMessage:

                    // timeline_archiveMessage
                    if (preg_match('#^/timeline/message/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_timeline_archiveMessage;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_archiveMessage')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::archiveMessageAction',  '_format' => 'json',));
                    }
                    not_timeline_archiveMessage:

                    if (0 === strpos($pathinfo, '/timeline/messages')) {
                        // timeline_getMessages
                        if (preg_match('#^/timeline/messages/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_timeline_getMessages;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_getMessages')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::getMessagesAction',  '_format' => 'json',));
                        }
                        not_timeline_getMessages:

                        // timeline_getLastMessages
                        if (preg_match('#^/timeline/messages/(?P<id>\\d+)/(?P<offset>\\d+)/(?P<limit>\\d+)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_timeline_getLastMessages;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_getLastMessages')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::getLastMessagesAction',  '_format' => 'json',));
                        }
                        not_timeline_getLastMessages:

                    }

                    // timeline_getComments
                    if (0 === strpos($pathinfo, '/timeline/message/comments') && preg_match('#^/timeline/message/comments/(?P<id>\\d+)/(?P<messageId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_timeline_getComments;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_getComments')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::getCommentsAction',  '_format' => 'json',));
                    }
                    not_timeline_getComments:

                }

                if (0 === strpos($pathinfo, '/timeline/comment')) {
                    // timeline_postComment
                    if (preg_match('#^/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_timeline_postComment;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_postComment')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::postCommentAction',  '_format' => 'json',));
                    }
                    not_timeline_postComment:

                    // timeline_editComment
                    if (preg_match('#^/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_timeline_editComment;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_editComment')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::editCommentAction',  '_format' => 'json',));
                    }
                    not_timeline_editComment:

                    // timeline_deleteComment
                    if (preg_match('#^/timeline/comment/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_timeline_deleteComment;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'timeline_deleteComment')), array (  '_controller' => 'SQLBundle\\Controller\\TimelineController::deleteCommentAction',  '_format' => 'json',));
                    }
                    not_timeline_deleteComment:

                }

            }

        }

        if (0 === strpos($pathinfo, '/user')) {
            // user_basicInformations
            if ($pathinfo === '/user') {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_user_basicInformations;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\UserController::basicInformationsAction',  '_format' => 'json',  '_route' => 'user_basicInformations',);
            }
            not_user_basicInformations:

            // user_getUserBasicInformations
            if (preg_match('#^/user/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_getUserBasicInformations;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_getUserBasicInformations')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::getUserBasicInformationsAction',  '_format' => 'json',));
            }
            not_user_getUserBasicInformations:

            if (0 === strpos($pathinfo, '/user/id')) {
                // user_getIdByName
                if (preg_match('#^/user/id/(?P<firstname>[^/]++)/(?P<lastname>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_user_getIdByName;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_getIdByName')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::getIdByNameAction',  '_format' => 'json',));
                }
                not_user_getIdByName:

                // user_getIdByEmail
                if (preg_match('#^/user/id/(?P<email>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_user_getIdByEmail;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_getIdByEmail')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::getIdByEmailAction',  '_format' => 'json',));
                }
                not_user_getIdByEmail:

            }

            // user_getUserAvatar
            if (0 === strpos($pathinfo, '/user/avatar') && preg_match('#^/user/avatar/(?P<userId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_getUserAvatar;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_getUserAvatar')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::getUserAvatarAction',  '_format' => 'json',));
            }
            not_user_getUserAvatar:

            // user_getAllProjectUserAvatar
            if (0 === strpos($pathinfo, '/user/project/avatars') && preg_match('#^/user/project/avatars/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_getAllProjectUserAvatar;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_getAllProjectUserAvatar')), array (  '_controller' => 'SQLBundle\\Controller\\UserController::getAllProjectUserAvatarAction',  '_format' => 'json',));
            }
            not_user_getAllProjectUserAvatar:

        }

        if (0 === strpos($pathinfo, '/whiteboard')) {
            // whiteboard_list
            if (0 === strpos($pathinfo, '/whiteboards') && preg_match('#^/whiteboards/(?P<projectId>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_whiteboard_list;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_list')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::listWhiteboardAction',  '_format' => 'json',));
            }
            not_whiteboard_list:

            // whiteboard_new
            if ($pathinfo === '/whiteboard') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_whiteboard_new;
                }

                return array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::newWhiteboardAction',  '_format' => 'json',  '_route' => 'whiteboard_new',);
            }
            not_whiteboard_new:

            // whiteboard_open
            if (preg_match('#^/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_whiteboard_open;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_open')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::openWhiteboardAction',  '_format' => 'json',));
            }
            not_whiteboard_open:

            // whiteboard_close
            if (preg_match('#^/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_whiteboard_close;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_close')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::closeWhiteboardAction',  '_format' => 'json',));
            }
            not_whiteboard_close:

            if (0 === strpos($pathinfo, '/whiteboard/draw')) {
                // whiteboard_pushDraw
                if (preg_match('#^/whiteboard/draw/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_whiteboard_pushDraw;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_pushDraw')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::pushDrawAction',  '_format' => 'json',));
                }
                not_whiteboard_pushDraw:

                // whiteboard_pullDraw
                if (preg_match('#^/whiteboard/draw/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_whiteboard_pullDraw;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_pullDraw')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::pullDrawAction',  '_format' => 'json',));
                }
                not_whiteboard_pullDraw:

            }

            // whiteboard_delete
            if (preg_match('#^/whiteboard/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_whiteboard_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_delete')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::delWhiteboardAction',  '_format' => 'json',));
            }
            not_whiteboard_delete:

            // whiteboard_deleteObject
            if (0 === strpos($pathinfo, '/whiteboard/object') && preg_match('#^/whiteboard/object/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_whiteboard_deleteObject;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'whiteboard_deleteObject')), array (  '_controller' => 'SQLBundle\\Controller\\WhiteboardController::deleteObjectAction',  '_format' => 'json',));
            }
            not_whiteboard_deleteObject:

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
