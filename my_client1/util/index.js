import commonApi from './api/common.js'
import orderApi from './api/order'
import resourceApi from './api/resource.js'
import courseApi from './api/course.js'
import articleApi from './api/article.js'
import documentApi from './api/document.js'
import shopApi from './api/shop.js'
import userApi from './api/user.js'
import examApi from './api/exam.js'
import integralApi from './api/integral.js'
import helpApi from './api/help.js'
import configApi from './api/config.js'
import memberApi from './api/member.js'
import messageApi from './api/message.js'
import countdownApi from './api/countdown.js'
import versionUpdateApi from './api/version_update.js'
import commentApi from './api/comment.js'


export default {
	...commonApi,
	...examApi,
	...orderApi,
	...resourceApi,
	...courseApi,
	...documentApi,
	...articleApi,
	...shopApi,
	...userApi,
	...integralApi,
	...helpApi,
	...configApi,
	...memberApi,
	...messageApi,
	...countdownApi,
	...versionUpdateApi,
	...commentApi
}